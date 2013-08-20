ShortE PHP form-generator
===============================

A PHP stdClass oriented-way of creating HTML/XML (especially forms). 
Each attribute added to object will appear as attribute in rendered XML tag.


## Example 1:
```php
$attributes = array('class' => 'random honey bun');

$elements = array(
  'name' => array('Name', 'text', $attributes),
  'email' => array('Email', 'email', $attributes),
  'email-conf' => array('Email Again', 'email', array('value' => 'john@doe.com')),
  'pass' => array('Password', 'password'),
  'pass-conf' => array('Password Again', 'password'),
  'user_id' => array('123', 'hidden'),
);

$form = InputElement::form($elements); // Can also do InputElement::batch($elements) for raw input objects
$form->action = '/process-form.php';
print $form;
```

## Example 1 output:
```html
<form action="/process-form.php" method="post" ><label for="name" >Name</label>
<input class="random honey bun" id="name" name="text-name" type="text" /><label for="email" >Email</label>
<input class="random honey bun" id="email" name="email-email" type="email" /><label for="email-conf" >Email Again</label>
<input id="email-conf" name="email-email-conf" type="email" value="john@doe.com" /><label for="pass" >Password</label>
<input id="pass" name="password-pass" type="password" /><label for="pass-conf" >Password Again</label>
<input id="pass-conf" name="password-pass-conf" type="password" /><input id="user_id" name="hidden-user_id" type="hidden" value="123" /></form>
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

foreach($textfields as $element_id => $label_text) {
  $inputs[$element_id] = new InputElement('text', '', $attributes);
  $inputs[$element_id]->id = $element_id;
  $inputs[$element_id]->prefix = new Element('label', $label_text, array('for' => $element_id));
}

print new Element('form', implode("\n", $inputs));
```

## Example 2 output:
```html
<form><label for="name" >Name</label>
<input class="textfields" id="name" type="text" />
<label for="email" >Email</label>
<input class="textfields" id="email" type="text" />
<label for="email-conf" >Email Again</label>
<input class="textfields" id="email-conf" type="text" />
<label for="pass" >Password</label>
<input class="textfields" id="pass" type="text" />
<label for="pass-conf" >Password Again</label>
<input class="textfields" id="pass-conf" type="text" /></form>
```


## Example 3: 
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

## Example 3 output:
```html
<form method="post" ><label for="body-field" >Body Field</label>
<input class="pretty-form" id="title-field" type="text" /> <label for="body-field" >Body Field</label>
<textarea class="pretty-form" id="body-field" >Default text goes here...</textarea></form>
</form>
```