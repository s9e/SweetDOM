#!/usr/bin/php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

function export($var)
{
	$str = var_export($var, true);

	return ($str === 'NULL') ? 'null' : $str;
}

function getTraits(string $fqn): array
{
	$traits = [];
	foreach (class_uses($fqn) as $trait)
	{
		$traits[$trait] = $trait;
		$traits += getTraits($trait);
	}

	return $traits;
}

function exportMethodParameters(ReflectionMethod $method): string
{
	$parameters = [];
	foreach ($method->getParameters() as $parameter)
	{
		$parameters[] = $parameter->getType() . ' $' . $parameter->name . ($parameter->isOptional() ? ' = ' . export($parameter->getDefaultValue()) : '');
	}

	return implode(', ', $parameters);
}

function exportMethodReturnType(ReflectionMethod $method): string
{
	$type = $method->getReturnType();

	return ($type instanceof ReflectionType) ? exportReflectionType($type) : 'mixed';
}

function exportReflectionType(ReflectionType $type): string
{
	if ($type instanceof ReflectionNamedType)
	{
		return ($type->allowsNull() ? '?' : '') . $type->getName();
	}
	if ($type instanceof ReflectionUnionType)
	{
		return implode('|', array_map('exportReflectionType', $type->getTypes()));
	}
	if ($type instanceof ReflectionUnionType)
	{
		return implode('&', array_map('exportReflectionType', $type->getTypes()));
	}

	die('Unsupported reflection type ' . get_class($type) . "\n");
}

$class   = new ReflectionClass('s9e\\SweetDOM\\NodeCreator');
$targets = [];
foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
{
	if (!preg_match('(^create(\\w+)$)', $method->name, $m))
	{
		continue;
	}

	$returnType = str_replace('s9e\\SweetDOM\\', '', (string) $method->getReturnType());
	if ($returnType === 'DocumentFragment')
	{
		// Document fragments are never returned themselves. Magic methods that return something
		// will either return the first node that was inserted, or nothing (null)
		$returnType = 'mixed';
	}

	$methodName = '$ACTION' . $m[1];
	$annotation = $returnType . ' ' . $methodName . '(' . exportMethodParameters($method) . ')';

	$targets[$methodName] = $annotation;
}
ksort($targets);

// Build a map of [native class => extended class]
$classMap = [];
foreach (glob(__DIR__ . '/../src/*.php') as $filepath)
{
	$file = file_get_contents($filepath);
	if (preg_match('((\\w++) extends (DOM\\w+))', $file, $m))
	{
		$classMap[$m[2]] = $m[1];
	}
}

foreach (glob(__DIR__ . '/../src/Element.php') as $filepath)
{
	$filepath = realpath($filepath);
	$file = file_get_contents($filepath);
	if (!preg_match('((\\w++) extends (DOM\\w+))', $file, $m))
	{
		continue;
	}

	$annotations     = [];
	$className       = 's9e\\SweetDOM\\' . $m[1];
	$parentClassName = $m[2];

	if ($parentClassName === 'DOMDocument')
	{
		// https://github.com/php/php-src/issues/12440
		$annotations = [
			'mcreateAttribute' => "\n* @method Attr|false createAttribute(string \$localName)",
			'mcreateComment' => "\n* @method Comment createComment(string \$data)",
			'mcreateAttributeNS' => "\n* @method Attr|false createAttributeNS(?string \$namespace, string \$qualifiedName)",
			'mcreateCDATASection' => "\n* @method CdataSection|false createCDATASection(string \$data)",
			'mcreateDocumentFragment' => "\n* @method DocumentFragment createDocumentFragment()",
			'mcreateElement' => "\n* @method Element|false createElement(string \$localName, string \$value = '')",
			'mcreateElementNS' => "\n* @method Element|false createElementNS(?string \$namespace, string \$qualifiedName, string \$value = '')",
			'mcreateTextNode' => "\n* @method Text createTextNode(string \$data)",
			'mgetElementById' => "\n* @method ?Element getElementById(string \$elementId)"
		];
	}

	// Collect all methods and properties from the parent class and fix the return type expected
	// from the extended class (DOM<X> => s9e\SweetDOM\<X>)
	$parentClass = new ReflectionClass($parentClassName);
	foreach ($parentClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
	{
		$oldType = exportMethodReturnType($method);
		$newType = strtr($oldType, $classMap);
		if ($newType !== $oldType)
		{
			$annotations['m' . $method->name] = "\n* @method " . $newType . ' ' . $method->name . '(' . exportMethodParameters($method) . ')';
		}
	}
	foreach ($parentClass->getProperties(ReflectionMethod::IS_PUBLIC) as $property)
	{
		$oldType = (string) $property->getType();
		$newType = strtr($oldType, $classMap);
		if ($newType !== $oldType)
		{
			$annotations['p' . $property->name] = "\n* @property " . $newType . ' $' . $property->name;
		}
	}

	// Add magic methods
	if (array_key_exists('s9e\\SweetDOM\\NodeTraits\\MagicMethods', getTraits($className)))
	{
		$actions = array_intersect(
			['after', 'append', 'before', 'prepend', 'replaceWith'],
			get_class_methods($className)
		);
		foreach ($actions as $action)
		{
			foreach ($targets as $methodName => $target)
			{
				$methodName = str_replace('$ACTION', $action, $methodName);
				$annotations['m' . $methodName] = "\n* @method " . str_replace('$ACTION', $action, $target);
			}
		}
	}

	ksort($annotations);
	$newFile = preg_replace_callback(
		'(/\\*\\*\\K(?:\\n\\* \\N++)*+(?=\\n\\*/\\nclass))s',
		fn() => implode('', $annotations),
		$file
	);
	if ($newFile !== $file)
	{
		file_put_contents($filepath, $newFile);
		echo "Patched $filepath\n";
	}
}