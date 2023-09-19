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
	$annotation = $m[1] . '(';

	$parameters = [];
	foreach ($method->getParameters() as $parameter)
	{
		$parameters[] = $parameter->getType() . ' $' . $parameter->name . ($parameter->isOptional() ? ' = ' . export($parameter->getDefaultValue()) : '');
	}
	$annotation .= implode(', ', $parameters) . '): ' . str_replace('s9e\\SweetDOM\\', '', (string) $method->getReturnType());
	var_dump($annotation);

}
exit;
foreach (glob(__DIR__ . '/../src/*php') as $filepath)
{
	$file = file_get_contents($filepath);
	if (!preg_match('( extends (DOM\\w+))', $file, $m))
	{
		continue;
	}
}