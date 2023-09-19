#!/usr/bin/php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

function export($var)
{
	$str = var_export($var, true);

	return ($str === 'NULL') ? 'null' : $str;
}

$class   = new ReflectionClass('s9e\\SweetDOM\\NodeCreator');
$targets = [];
foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
{
	if (!preg_match('(^create(\\w+)$)', $method->name, $m))
	{
		continue;
	}
	$annotation = str_replace('s9e\\SweetDOM\\', '', (string) $method->getReturnType()) . ' $ACTION' . $m[1] . '(';

	$parameters = [];
	foreach ($method->getParameters() as $parameter)
	{
		$parameters[] = $parameter->getType() . ' $' . $parameter->name . ($parameter->isOptional() ? ' = ' . export($parameter->getDefaultValue()) : '');
	}
	$annotation .= implode(', ', $parameters) . ')';

	$targets[] = $annotation;
}
ksort($targets);

foreach (glob(__DIR__ . '/../src/*.php') as $filepath)
{
	$filepath = realpath($filepath);
	$file = file_get_contents($filepath);
	if (!preg_match('((\\w++) extends (DOM\\w+))', $file, $m) || $m[2] === 'DOMDocument')
	{
		continue;
	}

	$actions = array_intersect(
		['after', 'append', 'before', 'prepend', 'replaceWith'],
		get_class_methods('s9e\\SweetDOM\\' . $m[1])
	);
	$annotations = [];
	foreach ($actions as $action)
	{
		foreach ($targets as $target)
		{
			$annotations[] = '* @method ' . str_replace('$ACTION', $action, $target);
		}
		sort($annotations);
	}

	$newFile = preg_replace_callback(
		'(/\\*\\*\\n\\K(?:\\* \\N++)*(?=\\n\\*/\\nclass))s',
		fn() => implode("\n", $annotations),
		$file
	);
	if ($newFile !== $file)
	{
		file_put_contents($filepath, $newFile);
		echo "Patched $filepath\n";
	}
}