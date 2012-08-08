<?php

namespace Tactics\Bundle\AdminBundle\Show;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Show {
  
  const FIELD_TYPE_STRING = 'string';
  const FIELD_TYPE_OBJECT = 'object';
  const FIELD_TYPE_DATE = 'date';
  const FIELD_TYPE_DECIMAL = 'decimal';
  const FIELD_TYPE_MONEY = 'money';
  const FIELD_TYPE_NULL = 'empty';
  
  const CASE_LOWER = 'lower';
  const CASE_UPPER = 'upper';
  const CASE_UCFIRST = 'ucfirst';
  const CASE_UCWORDS = 'ucwords';
    
  protected $object = null;
  protected $object_peer = null;
  
  protected $container = null;
  
  protected $is_collapsed = false;
  
  protected $tabs = array();
  
  protected $left_fields = array();
  protected $left_hidden_fields = array();
  protected $right_fields = array();
  protected $right_hidden_fields = array();
      
  public function __construct($object, ContainerInterface $container)
  {
    $this->setObject($object);
    $this->setObjectPeer();
    $this->setContainer($container);
  }
  
  /**
   * Maakt een veld aan.
   * 
   * @param $field
   * @param strin $field_type
   * @param array $options
   * @return array
   */
  protected function createField($field, $field_type, $options)
  {
    // Eerste methode maken om waarde op te halen.
    $getMethod = $this->getMethod($field);
    // Waarde ophalen:
    $value = $this->getObject()->$getMethod();
    
    // als field_type niet gezet is, dit automatisch gaan bepalen:
    if (! $field_type)
    {
      $field_type = $this->guessFieldType($value);
    }
    
    // als value leeg is, field_type_null zetten (guessFieldType houdt hier rekening mee,
    // als er een type specifiek gegeven wordt, gebeurt dit niet.
    if (! $value)
    {
      $field_type = self::FIELD_TYPE_NULL;
    }
    
    // label maken adhv parameter, of zelf aanmaken adhv veld.
    if (! isset($options['field_label']))
    {
      $field_label = $this->formatFieldName($field);
    }
    else
    {
      $field_label = $options['field_label'];
    }
    
    // veld teruggeven
    return array(
      'label' => $field_label,
      'value' => $this->formatField($value, $field_type, $options)
    );
  }
  
  /**
   * Bepaalt het type van een field.
   * 
   * @param string $field_type
   * @return string
   */
  protected function guessFieldType($value)
  {
    //string
    if (is_string($value))
    {
      $field_type = self::FIELD_TYPE_STRING;
    }
    // object
    elseif (is_object($value))
    {
      // DateTime
      if ($value instanceof \DateTime)
      {
        $field_type = self::FIELD_TYPE_DATE;
      }
      // andere objecten
      else
      {
        $field_type = self::FIELD_TYPE_OBJECT;
      }
    }
    // decimalen
    elseif (is_float($value))
    {
      $field_type = self::FIELD_TYPE_DECIMAL;
    }
    // null
    elseif (! $value)
    {
      $field_type = self::FIELD_TYPE_NULL;
    }
    // geen bestaand type wordt gewoon string.
    else
    {
      $field_type = self::FIELD_TYPE_STRING;
    }
    
    return $field_type;
  }
  
  /**
   * Gaat een label formatten adhv naam in class.
   * 
   * @param string $field_name
   * @param string $class_name
   * @return string
   */
  protected function formatFieldname($field_name)
  {
    $formatted = str_replace('_', ' ', $this->getObjectPeer()->translateFieldname($field_name, \BasePeer::TYPE_COLNAME, \BasePeer::TYPE_FIELDNAME));

    return ucfirst($formatted); 
  }
  
  /**
   * Format het veld voor correcte weergave.
   * 
   * @param  var
   * @return string
   */
  protected function formatField($value, $field_type, $options = array())
  {
    
    switch($field_type)
    {
      case self::FIELD_TYPE_STRING:
        $field_value = $this->formatStringField($value, $options);
        break;
      case self::FIELD_TYPE_DATE:
        $field_value = $this->formatDateField($value, $options);
        break;
      case self::FIELD_TYPE_DECIMAL:
        $field_value = $this->formatDecimalField($value, $options);
        break;
      case self::FIELD_TYPE_MONEY:
        $field_value = $this->formatMoneyField($value, $options);
        break;
      case self::FIELD_TYPE_OBJECT:
        $field_value = $this->formatObjectField($value, $options);
        break;
      case self::FIELD_TYPE_NULL:
        $field_value = $this->formatNullField($value, $options);
        break;
    }
    
    return $field_value;
  }
  
  protected function formatStringField($value, $options = array())
  {
    $value = strtolower($value);
    
    if (! isset($options['case']))
    {
      $value = ucfirst($value);
    }
    else
    {
      switch($options['case'])
      {
        case self::CASE_LOWER: 
          $value = strtolower($value);
          break;
        case self::CASE_UPPER:
          $value = strtoupper($value);
          break;
        case self::CASE_UCFIRST:
          $value = ucfirst($value);
          break;
        case self::CASE_UCWORDS:
          $value = ucwords($value);
          break;
        default:
          $value = ucfirst($value);
      }
    }
    
    if (isset($options['sprintf']))
    {
      $value = sprintf($options['sprintf'], $value);
    }
    
    if (isset($options['nl2br']))
    {
      $value = nl2br($value);
    }
    
    return $value;
  }
  
  protected function formatDateField($value, $options = array())
  {
    if (! isset($options['format']))
    {
      $value = $value->format('d/m/Y');
    }
    else
    {
      $value = $value->format($options['format']);
    }
    
    return $value;
  }
  
  protected function formatDecimalField($value, $options = array())
  {
    
    if (! isset($options['precision']))
    {
      $split = explode('.', $value);
      $precision = strlen($split[1]);
    }
    else
    {
      $precision = $options['precision'];
    }
    
    $decimal_separator = isset($options['decimal_separator']) ? $options['decimal_separator'] : ',';
    $thousands_separator = isset($options['thousands_separator']) ? $options['thousands_separator'] : ' ';
    
    return number_format($value, $precision, $decimal_separator, $thousands_separator);
  }
  
  protected function formatMoneyField($value, $options = array())
  {    
    $precision = isset($options['precision']) ? $options['precision'] : 2;
    $decimal_separator = isset($options['decimal_separator']) ? $options['decimal_separator'] : ',';
    $thousands_separator = isset($options['thousands_separator']) ? $options['thousands_separator'] : ' ';
    
    return number_format($value, $precision, $decimal_separator, $thousands_separator);
  }
  
  protected function formatObjectField($value, $options = array())
  {
    if (! isset($options['method']))
    {
      
      if (method_exists($value, '_toString'))
      {
        $field_value = $value;
      }
      else
      {
        $field_value = $this->getObjectClass($value) . ' ' . $value->getId();
      }
    }
    else
    {
      $field_value = $value->$options['method']();
    }
    
    return $field_value;
  }
  
  protected function formatNullField($value, $options = array())
  {
    
    $field_value = isset($options['null_value']) ? $options['null_value'] : 'Waarde onbekend';
    
    return $field_value;
  }
  
  /**
   * Haalt de methode op waarmee de waarde van een veld opgehaald wordt.
   * 
   * @param string $field_name
   * @return string
   */
  protected function getMethod($field_name)
  {
    $method = str_replace('_', ' ', $this->getObjectPeer()->translateFieldname($field_name, \BasePeer::TYPE_COLNAME, \BasePeer::TYPE_PHPNAME));
    
    return 'get' . ucfirst($method);
  }
  
  /**
   * Zet het object dag getoond wordt.
   * 
   * @param Object $object 
   */
  public function setObject($object)
  {
    $this->object = $object;
  }
  
  /**
   * Geeft het object dat getoond wordt terug.
   * 
   * @return Object
   */
  public function getObject()
  {
    return $this->object;
  }
  
  /**
   * Zet de peer klasse van het object. 
   */
  public function setObjectPeer()
  {
    $peer_naam = get_class($this->getObject()) . 'Peer';
    
    $this->object_peer = new $peer_naam();
  }
  
  /**
   * Geeft de peer klasse van het object terug.
   * 
   * @return type 
   */
  public function getObjectPeer()
  {
    return $this->object_peer;
  }
  
  /**
   * Geeft de classname terug zonder namespace
   * 
   * @return string 
   */
  public function getObjectClass($object = null)
  {
    if ($object)
    {
      $reflection_class = new \ReflectionClass($object);
    }
    else
    {
      $reflection_class = new \ReflectionClass($this->getObject());
    }
    
    return $reflection_class->getShortName(); 
  }
  
  /**
   * Zet de container voor dit object.
   * 
   * @param ContainerInterface $container 
   */
  public function setContainer($container)
  {
    $this->container = $container;
  }
  
  /**
   * Geeft de container terug
   * 
   * @return ContainerInterface
   */
  public function getContainer()
  {
    return $this->container;
  }
  
  /**
   * Bepaalt of show standaard verborgen wordt of niet.
   * 
   * @param boolean $is_collapsed 
   */
  public function setIsCollapsed($is_collapsed = false)
  {
    $this->is_collapsed = $is_collapsed;
  }
  
  /**
   * Geeft terug of de show verborgen wordt of niet.
   * 
   * @return boolean
   */
  public function getIsCollapsed()
  {
    return $this->is_collapsed;
  }
    
  /**
   * Voegt een tab toe aan het Show object 
   */
  public function addTab($tab)
  {
    $this->tabs[] = $tab;
    
    return $this;
  }
  
  /**
   * Voegt een field toe aan de linkerkolom van het Show object. 
   * 
   * @param $field
   */
  public function addLeft($field, $field_type = null, $options = array())
  {
    
    $this->left_fields[] = $this->createField($field, $field_type, $options);
    
    return $this;
  }
  
  /**
   * Geeft de linkse velden terug.
   * 
   * @return array 
   */
  public function getLeft()
  {
    return $this->left_fields;
  }
  
  /**
   * Voegt een field toe aan de hidden linkerkolom van het Show object.
   */
  public function addLeftHidden($field, $field_type = null, $options = array())
  {
    $this->left_hidden_fields[] = $this->createField($field, $field_type, $options);
    
    return $this;
  }
  
  /**
   * Geeft de linkse hidden velden terug.
   * 
   * @return array 
   */
  public function getLeftHidden()
  {
    return $this->left_hidden_fields;
  }
  
  /**
   * Voegt een field toe aan de rechterkolom van het Show object.
   */
  public function addRight($field, $field_type = null, $options = array())
  {
    $this->right_fields[] = $this->createField($field, $field_type, $options);
    
    return $this;
  }
  
  /**
   * Geeft de rechtse velden terug.
   * 
   * @return array 
   */
  public function getRight()
  {
    return $this->right_fields;
  }
  
  /**
   * Voegt een field toe aan de hidden rechterkolom van het Show object.
   */
  public function addRightHidden($field, $field_type = null, $options = array())
  {
    $this->right_hidden_fields[] = $this->createField($field, $field_type, $options);
    
    return $this;
  }
  
  /**
   * Geeft de linkse velden terug.
   * 
   * @return array 
   */
  public function getRightHidden()
  {
    return $this->right_hidden_fields;
  }

  /**
   * Geeft terug of de show linkse velden heeft.
   * 
   * @return boolean 
   */
  public function hasLeftFields()
  {
    return count($this->left_fields) ? true : false;
  }

  /**
   * Geeft terug of de show linkse velden heeft.
   * 
   * @return boolean 
   */
  public function hasLeftHiddenFields()
  {
    return count($this->left_hidden_fields) ? true : false;
  }

  /**
   * Geeft terug of de show linkse velden heeft.
   * 
   * @return boolean 
   */
  public function hasRightFields()
  {
    return count($this->right_fields) ? true : false;
  }

  /**
   * Geeft terug of de show linkse velden heeft.
   * 
   * @return boolean 
   */
  public function hasRightHiddenFields()
  {
    return count($this->right_hidden_fields) ? true : false;
  }
  
  /**
   * Geeft terug of het object fields heeft.
   * 
   * @return boolean 
   */
  public function hasFields()
  {
    return $this->hasLeftFields() || $this->hasRightFields() ? true : false;
  }
  
  /**
   * Geeft terug of het object hidden fields heeft.
   * 
   * @return boolean 
   */
  public function hasHiddenFields()
  {
    return $this->hasLeftHiddenFields() || $this->hasRightHiddenFields() ? true : false;
  }
  
  /**
   * Rendert de show html.
   * 
   * @return string 
   */
  public function render()
  {
    return $this->getContainer()->get("templating")->render(
                "TacticsAdminBundle:Default:show.html.twig", array( 
                  'show' => $this,
                  'object' => $this->getObject()
                )
        );
  }
}

?>
