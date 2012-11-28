<?php

namespace Tactics\Bundle\AdminBundle\Show;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\Util\Inflector;

class Show implements ContainerAwareInterface
{
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
    protected $classMetaData = null;

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

    protected $spanwidth = 'span11';
    protected $dataActiveText = null;
    protected $dataInactiveText = null;

    /**
     * Constructor.
     */
    public function __construct($object, ContainerInterface $container, $options = array())
    {
        $this->setObject($object);
        $this->setContainer($container);

        if (!$this->getContainer()->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application.');
        }

        $doctrine = $this->getContainer()->get('doctrine');
        $cmf = $doctrine->getManager()->getMetaDataFactory();
        $this->classMetaData = $cmf->getMetaDataFor(get_class($object));

        if (isset ($options['spanwidth'])) {
            $this->setSpanWidth($options['spanwidth']);
        }

        if (isset($options['active-text'])) {
            $this->setDataActiveText($options['active-text']);
        }

        if (isset($options['inactive-text'])) {
            $this->setDataInactiveText($options['inactive-text']);
        }
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
        $associationMappings = $this->getClassMetaData()->getAssociationMappings();
      
        // When field is associated, retrieve associated object(s) and cast 
        // them to string.
        if (false !== array_key_exists($field, $associationMappings)) {
            $method = $this->getMethod($field);
            $targetEntity = $this->getObject()->$method();

            if ($targetEntity instanceof \Traversable) {
                $values = array();

                foreach ($targetEntity as $entity) {
                    $values[] = (string) $entity;
                }

                $value = $values ? implode(', ', $values) : ' - ';
            } elseif ($targetEntity) {
                $value = (string) $targetEntity;
            } else {
                $value = '';
            }
        } elseif ($this->getClassMetaData()->hasField($field)) { // Entity field
            $method = $this->getMethod($field);
            $value = $this->getObject()->$method();
        } else { // Custom field.
            $value = isset($options['value']) ? $options['value'] : $field;
            $field_label = isset($options['field_label']) ? $options['field_label'] : '';
        }

        if (isset($options['modifier'])) {
            $value = eval('return ' . $options['modifier'] . '($value);');
        }

        // als field_type niet gezet is, dit automatisch gaan bepalen:
        if (! $field_type) {
            $field_type = $this->guessFieldType($value);
        }

        // als value leeg is, field_type_null zetten (guessFieldType houdt hier rekening mee,
        // als er een type specifiek gegeven wordt, gebeurt dit niet.
        if (! $value) {
            $field_type = self::FIELD_TYPE_NULL;
        }

        // label maken adhv parameter, of zelf aanmaken adhv veld.
        if (! isset($options['field_label'])) {
            $field_label = $this->formatFieldName($field);
        } else {
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
        if (is_string($value)) {
            $field_type = self::FIELD_TYPE_STRING;
        }
        // object
        elseif (is_object($value)) {
            // DateTime
            if ($value instanceof \DateTime) {
                $field_type = self::FIELD_TYPE_DATE;
            }
            // andere objecten
            else {
                $field_type = self::FIELD_TYPE_OBJECT;
            }
        }
        // decimalen
        elseif (is_float($value)) {
            $field_type = self::FIELD_TYPE_DECIMAL;
        }
        // array
        elseif (is_array($value)) {
            $field_type = self::FIELD_TYPE_ARRAY;
        }
        // null
        elseif (! $value) {
            $field_type = self::FIELD_TYPE_NULL;
        }
        // geen bestaand type wordt gewoon string.
        else {
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
        return ucfirst(str_replace('_', ' ', $field_name));
    }

    /**
     * Format het veld voor correcte weergave.
     *
     * @param  var
     * @return string
     */
    protected function formatField($value, $field_type, $options = array())
    {

        switch ($field_type) {
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
        if (isset($options['case'])) {
            switch ($options['case']) {
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

        if (isset($options['sprintf'])) {
            $value = sprintf($options['sprintf'], $value);
        }

        if (isset($options['nl2br'])) {
            $value = nl2br($value);
        }

        return $value;
    }

    protected function formatDateField($value, $options = array())
    {
        if (! isset($options['format'])) {
            $value = $value->format('d/m/Y');
        } else {
            $value = $value->format($options['format']);
        }

        return $value;
    }

    protected function formatDecimalField($value, $options = array())
    {

        if (! isset($options['precision'])) {
            $split = explode('.', $value);
            $precision = strlen($split[1]);
        } else {
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

        if (! isset($options['method'])) {

            if (method_exists($value, '_toString')) {
                $field_value = $value;
            } else {
                $field_value = $this->getObjectClass($value) . ' ' . $value->getId();
            }
        } else {
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
        return 'get'.Inflector::camelize($field_name);
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
     * Geeft de classname terug zonder namespace
     *
     * @return string
     */
    public function getObjectClass($object = null)
    {
        if ($object) {
            $reflection_class = new \ReflectionClass($object);
        } else {
            $reflection_class = new \ReflectionClass($this->getObject());
        }

        return $reflection_class->getShortName();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return ClassMetaData, The ClassMetaData for object.
     */
    public function getClassMetaData()
    {
        return $this->classMetaData;
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
                'object' => $this->getObject(),
                'spanwidth' => $this->getSpanWidth()
            )
        );
    }

    /**
     * Sets the span attribute
     *
     * @param String $spanwidth a span class
     */
    private function setSpanWidth($spanwidth)
    {
        $this->spanwidth = $spanwidth;
    }

    /**
     * Gets the span attribute
     *
     * @return String $spanwidth a span class
     */
    private function getSpanWidth()
    {
        return $this->spanwidth;
    }

    /**
     * Returns the text that should be displayed when all fields are active
     *
     * @return String
     */
    public function getDataActiveText()
    {
        return $this->dataActiveText ? $this->dataActiveText : 'Toon minder';
    }

    /**
     * Sets the text that should be desplayed when all fields are active
     *
     * @param String $dataActiveText
     */
    public function setDataActiveText($dataActiveText)
    {
        $this->dataActiveText = $dataActiveText;
    }

    /**
     * Returns the text that should be displayed when all fields are inactive
     *
     * @return String
     */
    public function getDataInactiveText()
    {
        return $this->dataInactiveText ? $this->dataInactiveText : 'Toon meer';
    }

    /**
     * Sets the text that should be desplayed when all fields are active
     *
     * @param String $dataActiveText
     */
    public function setDataInactiveText($dataInactiveText)
    {
        $this->dataInactiveText = $dataInactiveText;
    }
}
