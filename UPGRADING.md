## From 2.x to 3.0

A number of `s9e\SweetDOM\Element` methods are now deprecated. Those deprecated methods remain in 3.x but may generate a `E_USER_DEPRECATED` notice. Most deprecated methods can be replaced with native calls, see the table below for a conversion guide. Deprecated methods will be removed in a future version.

Magic methods whose names end in `Sibling` are deprecated and have been renamed after the DOM methods `after` and `before`. Methods using the naming scheme `append{Element}Sibling` should be renamed `after{Element}`, and `prepend{Element}Sibling` should be renamed `before{Element}`.

Text methods `appendText`, `appendTextSibling`, `prependText`, and `prependTextSibling` are deprecated in favour of native DOM operations `append`, `after`, `prepend`, and `before`.

The method `insertAdjacentXML` is deprecated without a replacement planned. It can be replaced with native methods using [`DOMDocumentFragment::appendXML`](https://www.php.net/manual/domdocumentfragment.appendxml.php).

|               2.x               |           3.0           |
|---------------------------------|-------------------------|
| appendElementSibling            | afterElement            |
| appendText                      | append                  |
| appendTextSibling               | after                   |
| appendXslApplyTemplatesSibling  | afterXslApplyTemplates  |
| appendXslAttributeSibling       | afterXslAttribute       |
| appendXslChooseSibling          | afterXslChoose          |
| appendXslCommentSibling         | afterXslComment         |
| appendXslCopyOfSibling          | afterXslCopyOf          |
| appendXslIfSibling              | afterXslIf              |
| appendXslOtherwiseSibling       | afterXslOtherwise       |
| appendXslTextSibling            | afterXslText            |
| appendXslValueOfSibling         | afterXslValueOf         |
| appendXslVariableSibling        | afterXslVariable        |
| appendXslWhenSibling            | afterXslWhen            |
| prependElementSibling           | beforeElement           |
| prependText                     | prepend                 |
| prependTextSibling              | before                  |
| prependXslApplyTemplatesSibling | beforeXslApplyTemplates |
| prependXslAttributeSibling      | beforeXslAttribute      |
| prependXslChooseSibling         | beforeXslChoose         |
| prependXslCommentSibling        | beforeXslComment        |
| prependXslCopyOfSibling         | beforeXslCopyOf         |
| prependXslIfSibling             | beforeXslIf             |
| prependXslOtherwiseSibling      | beforeXslOtherwise      |
| prependXslTextSibling           | beforeXslText           |
| prependXslValueOfSibling        | beforeXslValueOf        |
| prependXslVariableSibling       | beforeXslVariable       |
| prependXslWhenSibling           | beforeXslWhen           |