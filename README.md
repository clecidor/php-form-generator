ShortE PHP form-generator
===============================

A PHP stdClass oriented-way of creating HTML/XML (especially forms). 
Each attribute added to object will appear as attribute in rendered XML tag.


## Example: 
```php
// Add title text-field
$attributes = array('class' => 'pretty-form');
$label = new Element('label', 'Title');
$input = new InputElement('text', $label, $attributes); // $label set to $input->inner

unset($input->inner); // unsetting $label that was added above.
$input->id = $label->for = 'title-field';
$input->prefix = $label;

// Add body textarea field
$label->inner = 'Body Field';
$textarea = new Element('textarea', 'Default text goes here...', $attributes);
$textarea->id = $label->for = 'body-field';
$textarea->prefix = $label;

// Add two fields to <form>
// Element::__toString() turns "$input $textarea" objects into XML string.
$form = new Element('form', "$input $textarea", array('enctype' => "multipart/form-data"));
$form->method = 'post';
unset($form->enctype); // didn't need it

print $form; // Element::__toString() turns $form object into XML string.

// Could also do within function(){}
return (string) $form;
```


## Another Example:
```php
$textfields = array(
  'name' => 'Name',
  'email' => 'Email',
  'email-conf' => 'Email Again',
  'pass' => 'Password',
  'pass-conf' => 'Password Again',
);

$inputs = array();
$attributes = array('class' => 'textfields');

foreach($textfields as $id => $label) {
  $inputs[$id] = new InputElement('text', '', $attributes);
  $inputs[$id]->id = $id;
  $inputs[$id]->prefix = new Element('label', $label, array('for' => $id));
}

print new Element('form', implode("\n", $inputs));
```
