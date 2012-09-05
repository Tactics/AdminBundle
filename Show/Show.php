<?php

namespace Tactics\Bundle\AdminBundle\Show;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \BasePeer;

class Show {

  const FIELD_TYPE_STRING = 'string';
  const FIELD_TYPE_OBJECT = 'object';
  const FIELD_TYPE_DATE = 'date';
  const FIELD_TYPE_DECIMAL = 'decimal';
  const FIELD_TYPE_MONEY = 'money';
  const FIELD_TYPE_ARRAY = 'array';
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

  protected $top_fields = array();
  protected $top_hidden_fields = array();
  protected $left_fields = array();
  protected $left_hidden_fields = array();
  protected $right_fields = array();
  protected $right_hidden_fields = array();
  protected $bottom_fields = array();
  protected $bottom_hidden_fields = array();

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
    // Indien het veld niet bekend is, betreft het een custom veld
    if(! $this->isObjectField($field))
    {
      // custum / veld teruggeven
      return array(
        'label' => (isset($options['field_label']) ? $options['field_label'] : ''),
        'value' => $field
      );
    }

    // Indien het veld een foreign key is, halen we het object op en standaard weergave __toString
    $relativeField = substr($field, strpos($field, '.')+1);
    $foreignFields = $this->object_peer->getTableMap()->getForeignKeys();
    if(isset($foreignFields[$relativeField]))
    {
      // Opzoeken van kolom
      $relatedColumn = $foreignFields[$relativeField]->getRelatedColumnName();
      // Opzoeken van class
      $relatedClassName = $foreignFields[$relativeField]->getRelation()->getForeignTable()->getClassname();
      // Methode om gerelateerd object id op te halen:
      $getRelatedMethod = $this->getMethod($field);

      // Opzoeken van gerelateerd object
      if ($this->getObject()->$getRelatedMethod()) {
          $foreignObject =  eval('return ' . ucfirst($relatedClassName) . 'Query::create()->findPk(' . $this->getObject()->$getRelatedMethod() . ');');
      } else {
          $foreignObject = null;
      }

      // Default waarde
      if($foreignObject)
      {
        $value  = $foreignObject->__toString();
      }
      else
      {
        $value = '-';
      }
      // Standaard label overschrijven (bv. PERSOON IPV PERSOON ID)
      if(! isset($options['field_label']))
      {
        $options['field_label'] = ucfirst($foreignFields[$relativeField]->getRelatedTableName());
      }
    }
    else
    {
      // Eerste methode maken om waarde op te halen.
      $getMethod = $this->getMethod($field);
      // Waarde ophalen:
      $value = $this->getObject()->$getMethod();
    }

    if(isset($options['modifier']))
    {
      $value = eval('return ' . $options['modifier'] . '($value);');
    }

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
    // array
    elseif (is_array($value)) {
        $field_type = self::FIELD_TYPE_ARRAY;
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
      case self::FIELD_TYPE_ARRAY:
        $field_value = $this->formatArrayField($value, $options);
        break;
      case self::FIELD_TYPE_NULL:
        $field_value = $this->formatNullField($value, $options);
        break;
    }

    return $field_value;
  }

  protected function formatStringField($value, $options = array())
  {
    if (isset($options['case']))
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

  /**
   * Formats an array field
   *
   * @param array $value
   * @param array $options
   *
   * @return string $field_value
   */
  protected function formatArrayField($value, $options = array())
  {
      $separator = isset($options['separator']) ? $options['separator'] : ', ';

      $field_value = implode($separator, $value);

      return $field_value;
  }

  protected function formatNullField($value, $options = array())
  {

    $field_value = isset($options['null_value']) ? $options['null_value'] : '-';

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
   * Voegt een veld toe aan de bovenzijde
   *
   * @param string $field
   * @param string $field_type
   * @param array $options
   *
   */
  public function addTop($field, $field_type = null, $options = array())
  {
    return $this->addField('top', false, $field, $field_type, $options);
  }

  /**
   * Voegt een veld toe aan de linkerzijde
   *
   * @param string $field
   * @param string $field_type
   * @param array $options
   *
   */
  public function addLeft($field, $field_type = null, $options = array())
  {
    return $this->addField('left', false, $field, $field_type, $options);
  }

  /**
   * Voegt een veld toe aan de rechterzijde
   *
   * @param string $field
   * @param string $field_type
   * @param array $options
   *
   */
  public function addRight($field, $field_type = null, $options = array())
  {
    return $this->addField('right', false, $field, $field_type, $options);
  }

  /**
   * Voegt een veld toe aan de onderzijde
   *
   * @param string $field
   * @param string $field_type
   * @param array $options
   *
   */
  public function addBottom($field, $field_type = null, $options = array())
  {
    return $this->addField('bottom', false, $field, $field_type, $options);
  }

  /**
   * Voegt een veld toe aan de bovenzijde (verborgen)
   *
   * @param string $field
   * @param string $field_type
   * @param array $options
   *
   */
  public function addTopHidden($field, $field_type = null, $options = array())
  {
    return $this->addField('top', true, $field, $field_type, $options);
  }

  /**
   * Voegt een veld toe aan de linkerzijde (verborgen)
   *
   * @param string $field
   * @param string $field_type
   * @param array $options
   *
   */
  public function addLeftHidden($field, $field_type = null, $options = array())
  {
    return $this->addField('left', true, $field, $field_type, $options);
  }

  /**
   * Voegt een veld toe aan de rechterzijde (verborgen)
   *
   * @param string $field
   * @param string $field_type
   * @param array $options
   *
   */
  public function addRightHidden($field, $field_type = null, $options = array())
  {
    return $this->addField('right', true, $field, $field_type, $options);
  }

  /**
   * Voegt een veld toe aan de onderzijde (verborgen)
   *
   * @param string $field
   * @param string $field_type
   * @param array $options
   *
   */
  public function addBottomHidden($field, $field_type = null, $options = array())
  {
    return $this->addField('bottom', true, $field, $field_type, $options);
  }

  /**
   * Voegt een field toe aan een bepaalde positie
   *
   * @param string $positie (top, left, right, bottom)
   * @param boolean $hidden
   * @param $field
   * @param (optional) $field_type
   * @param (optional) $options
   */
  public function addField($positie, $hidden, $field, $field_type = null, $options = array())
  {
    $veldParam = $positie . ($hidden ? '_hidden' : '') . '_fields';
    array_push($this->$veldParam, $this->createField($field, $field_type, $options));
    return $this;
  }

  /**
   * Geeft de velden terug van een bepaalde positie
   *
   * @param string $positie (top, left, right, bottom)
   * @param (optional) boolean $hidden
   *
   * @return array
   */
  public function getFields($positie, $hidden=false)
  {
    return eval('return $this->' . $positie . ($hidden ? '_hidden' : '') . '_fields;');
  }

  /**
   * Geeft terug of de show velden heeft
   *
   * @param string $positie (top, left, right, bottom)
   * @param boolean $hidden
   *
   * @return boolean
   */
  public function hasFieldsAtPosition($positie, $hidden = false)
  {
    $positieVelden = eval('return $this->' . $positie . ($hidden ? '_hidden' : '') . '_fields;');

    return count($positieVelden) ? true : false;
  }

  /**
   * Geeft terug of het object fields heeft.
   *
   * @return boolean
   */
  public function hasFields()
  {
    return ($this->hasFieldsAtPosition('top') ||
           $this->hasFieldsAtPosition('bottom') ||
            $this->hasFieldsAtPosition('left') ||
            $this->hasFieldsAtPosition('right')) ? true : false;
  }

  /**
   * Geeft terug of het object hidden fields heeft.
   *
   * @return boolean
   */
  public function hasHiddenFields()
  {
    return ($this->hasFieldsAtPosition('top', true) ||
           $this->hasFieldsAtPosition('bottom', true) ||
            $this->hasFieldsAtPosition('left', true) ||
            $this->hasFieldsAtPosition('right', true)) ? true : false;
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

  /**
   * Controleert of dit veld een objectveld is, zoniet, betreft het een custom veld
   *
   * @return Boolean TRUE/FALSE
   */
  private function isObjectField($field_name)
  {
    return in_array($field_name, $this->getObjectPeer()->getFieldNames(BasePeer::TYPE_COLNAME)) ? true : false;
  }
}

?>
