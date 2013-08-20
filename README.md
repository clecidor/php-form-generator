ShortE PHP form-generator
===============================

A PHP stdClass oriented-way of creating HTML/XML (especially forms). 
Each attribute added to object will appear as attribute in rendered XML tag.


## Example 1: 
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

## Example 1 output:
```html
<form method="post" id="simple-form" ><label for="title-field" >Title</label>
<input class="pretty-form" type="text" id="title-field" />
<label for="body-field" >Body Field</label>
<textarea class="pretty-form" id="body-field" >Default text goes here...</textarea>
</form>
```



## Example 2:
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

## Example 2 output:
```html
<form method="post" ><label for="name" >Name</label>
<input class="textfields" type="text" id="name" />
<label for="email" >Email</label>
<input class="textfields" type="text" id="email" />
<label for="email-conf" >Email Again</label>
<input class="textfields" type="text" id="email-conf" />
<label for="pass" >Password</label>
<input class="textfields" type="text" id="pass" />
<label for="pass-conf" >Password Again</label>
<input class="textfields" type="text" id="pass-conf" /></form>
```