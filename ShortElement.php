<?php

/**
 * @interface IElement
 */
interface IElement {
  public function __toString();
  public function tag();
  public function attributes($stringify = FALSE);
  public function render($closingTag = TRUE); // used in __toString()
}

/**
 * @class Element
 *
 * A PHP stdObject oriented-way of creating XML tags. Each attribute added to object will appear as attribute in rendered XML tag.
 *
 * Example:
 * // Add title text-field
 * $attributes = array('class' => 'pretty-form');
 * $label = new Element('label', 'Title');
 * $input = new InputElement('text', $label, $attributes); // $label set to $input->inner
 *
 * unset($input->inner); // unsetting $label that was added above.
 * $input->id = $label->for = 'title-field';
 * $input->prefix = $label;
 *
 * // Add body textarea field
 * $label->inner = 'Body Field';
 * $textarea = new Element('textarea', 'Default text goes here...', $attributes);
 * $textarea->id = $label->for = 'body-field';
 * $textarea->prefix = $label;
 *
 * // Add two fields to <form>
 * // Element::__toString() magic-method turns "$input $textarea" into XML =)
 * $form = new Element('form', "$input $textarea", array('enctype' => "multipart/form-data"));
 * $form->method = 'post';
 * unset($form->enctype); // didn't need it
 *
 * print $form; // Element::__toString() magic-method turns $form into XML =)
 *
 * // Could also do within function(){}
 * return (string) $form;
 * 
 */
class Element extends stdClass implements IElement {
  protected $tag;
  public $inner = '';
  
  public function __construct($tag, $innerContents = '', array $attributes = array()) {
    $this->tag = (string) $tag;
    
    $this->inner = ($innerContents instanceof Element) ? $innerContents : trim((string) $innerContents);
    
    foreach($attributes as $key => $value) {
      $clean_key = str_ireplace(' ', '', strtolower($key));
      
      if (in_array($clean_key, array('prefix', 'suffix'))) {
        $this->{$clean_key} = ($value instanceof Element) ? $value : trim((string) $value);
      }
      else {
        $this->{$key} = trim((string) $value);
      }
    }
  }
  
  public function __toString() {
    return (string) $this->render($closingTag = TRUE);
  }
  
  final public function tag() {
    return (string) $this->tag;
  }
  
  final public function attributes($stringify = FALSE) {
    $attributes = get_object_vars($this);
    ksort($attributes); // alphabetical order
    
    $reserved_keys = array('tag', 'inner', 'prefix', 'suffix');
    
    if ($stringify) {
      $toSting = array();
      foreach($attributes as $key => $value) {
        if (!in_array(strtolower(trim($key)), $reserved_keys)) {
          $toSting[] = sprintf('%s="%s"', $key, (string) $value);
        }
      }
      
      return implode(' ', $toSting);
    }
    
    return $attributes;
  }
  
  public function render($closingTag = TRUE) {
    return $this->_render($closingTag);
  }
  
  final protected function _render($closingTag = TRUE) {
    $format = $closingTag ? '<%1$s%2$s>%3$s</%1$s>' : '<%1$s%2$s/> %3$s';
    
    $attributes = trim((string) $this->attributes($stringify = TRUE));
    
    if (!empty($attributes)) $attributes = " $attributes "; // pad with spaces...
    
    foreach(array('prefix', 'inner', 'suffix') as $reserved_key) {
      if (isset($this->{$reserved_key}) && ($this->{$reserved_key} instanceof Element)) {
        $element =& $this->{$reserved_key};
        $element->render();
      }
    }
    
    $innerContents = isset($this->inner) ? $this->inner : '';
    $xml = trim(sprintf($format, (string) $this->tag(), $attributes, (string) $innerContents));
    
    $prefix = isset($this->prefix) ? "{$this->prefix}\n" : '';
    $suffix = isset($this->suffix) ? "\n{$this->suffix}" : '';
    
    return sprintf("%s%s%s", (string) $prefix, (string) $xml, (string) $suffix);
  }
}

/**
 * @class ShortElement
 *
 * ShortElement is an element without closing-tag (i.e. <img /> <input /> <br /> etc).
 * Can still use ShortElement::render($closingTag = TRUE) as a remedy.
 */
class ShortElement extends Element {
  final public function __toString() {
    return (string) $this->render($closingTag = FALSE);
  }
  
  final public function render($closingTag = FALSE) {
    return (string) parent::render($closingTag);
  }
}

/**
 * @class InputElement
 * 
 * Wrapper that adapts ShortElement into defacto <input> tag. First parameter is now input-type instead of a tagname.
 */ 
class InputElement extends ShortElement {
  final public function __construct($type, $innerContents = '', array $attributes = array()) {
    $attributes['type'] = $type;
    parent::__construct('input', $innerContents, $attributes);
  }
  
  public static function batch(array $inputs) {
    $elements = array();
    
    foreach($inputs as $element_id => $input) {
      if ($input instanceof Element) {
        $element = $input;
      }
      else if (is_array($input)) {
        $type = (string) array_shift($input);
        $label_text = (string) array_shift($input);
        $attributes = (array) array_shift($input);
        
        $label = new Element('label', $label_text, array('for' => $element_id));
        $element = new InputElement($type, '', (array) $attributes);
        $label->for = $element->id = $element_id;
        
        $element->name = $element_id; //"{$type}_{$element_id}";
        
        ($type == 'hidden') ? ($element->value = $label_text) : ($element->prefix = $label);
      }
      
      $elements[$element_id] = $element;
    }
    
    return $elements;
  }
  
  public static function form(array $inputs) {
    $form = new Element('form', '', array('method' => 'post'));
    
    $elements = static::batch($inputs);
    
    while($element = array_shift($elements)) {
      if('file' == strtolower($element->type)) {
        $form->enctype = 'multipart/form-data';
      }
      $form->inner .= (string) $element;
    }
    
    return $form;
  }
}
