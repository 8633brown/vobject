<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject\Component,
    Sabre\VObject\Component\VCalendar,
    Sabre\VObject\Component\VCard,
    Sabre\Xml as SabreXml;

/**
 * XML Parser.
 *
 * This parser parses both the xCal and xCard formats.
 *
 * @copyright Copyright (C) 2007-2014 fruux GmbH. All rights reserved.
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class XML extends Parser {

    const XCAL_NAMESPACE  = 'urn:ietf:params:xml:ns:icalendar-2.0';
    const XCARD_NAMESPACE = 'urn:ietf:params:xml:ns:vcard-4.0';

    /**
     * The input data.
     *
     * @var array
     */
    protected $input;

    /**
     * A pointer/reference to the input.
     *
     * @var array
     */
    private $pointer;

    /**
     * Document, root component.
     *
     * @var Sabre\VObject\Document
     */
    protected $root;

    /**
     * Creates the parser.
     *
     * Optionally, it's possible to parse the input stream here.
     *
     * @param mixed $input
     * @param int $options Any parser options (OPTION constants).
     * @return void
     */
    public function __construct($input = null, $options = 0) {

        if (0 === $options) {
            $options = parent::OPTION_FORGIVING;
        }

        parent::__construct($input, $options);

    }

    /**
     * Parse xCal or xCard.
     *
     * @param resource|string $input
     * @param int $options
     * @throws \Exception
     * @return Sabre\VObject\Document
     */
    public function parse($input = null, $options = 0) {

        if (!is_null($input)) {
            $this->setInput($input);
        }

        if (0 !== $options) {
            $this->options = $options;
        }

        if (is_null($this->input)) {
            throw new EofException('End of input stream, or no input supplied');
        }

        if ($this->input['name'] === '{' . self::XCAL_NAMESPACE . '}icalendar') {

            $this->root = new VCalendar([], false);
            $this->pointer = &$this->input['value'][0];
            $this->parseVcalendarComponents($this->root);

        } else {
            throw new \Exception('Unsupported XML standard');
        }

        return $this->root;
    }

    /**
     * Parse a vCalendar.
     *
     * @param Sabre\VObject\Component $parentComponent
     * @return void
     */
    protected function parseVCalendarComponents(Component $parentComponent) {

        foreach ($this->pointer['value'] ?: [] as $children) {

            switch (static::getTagName($children['name'])) {

                case 'properties':
                    $this->pointer = &$children['value'];
                    $this->parseProperty($parentComponent);
                    break;

                case 'components':
                    $this->pointer = &$children;
                    $this->parseComponent($parentComponent);
                    break;
            }
        }

    }

                            foreach ($xmlParameters as $xmlParameter) {
                                $propertyParameters[static::getTagName($xmlParameter['name'])]
                                    = $xmlParameter['value'][0]['value'];
                            }

                            array_splice($xmlProperty['value'], $i, 1);

                        }

    protected function parseProperty(Component $parentComponent) {

        foreach ($this->pointer as $xmlProperty) {

            $propertyName       = static::getTagName($xmlProperty['name']);
            $propertyValue      = [];
            $propertyParameters = [];
            $propertyType       = 'text';

            foreach ($xmlProperty['value'] as $i => $xmlPropertyChild) {

                if (   !is_array($xmlPropertyChild)
                    || 'parameters' !== static::getTagName($xmlPropertyChild['name']))
                    continue;

                foreach ($xmlParameters as $xmlParameter) {
                    $propertyParameters[static::getTagName($xmlParameter['name'])]
                        = $xmlParameter['value'][0]['value'];
                }

                array_splice($xmlProperty['value'], $i, 1);

            }

            switch ($propertyName) {

                case 'geo':
                    $propertyType               = 'float';
                    $propertyValue['latitude']  = 0;
                    $propertyValue['longitude'] = 0;

                    foreach ($xmlProperty['value'] as $xmlRequestChild) {
                        $propertyValue[static::getTagName($xmlRequestChild['name'])]
                            = $xmlRequestChild['value'];
                    }
                    break;

                case 'request-status':
                    $propertyType = 'text';

                    foreach ($xmlProperty['value'] as $xmlRequestChild) {
                        $propertyValue[static::getTagName($xmlRequestChild['name'])]
                            = $xmlRequestChild['value'];
                    }
                    break;

                case 'freebusy':
                    $propertyType = 'freebusy';
                    // We don't break because we only want to set
                    // another property type.

                case 'categories':
                case 'resources':
                case 'exdate':
                    foreach ($xmlProperty['value'] as $specialChild) {
                        $propertyValue[static::getTagName($specialChild['name'])]
                            = $specialChild['value'];
                    }
                    break;

                case 'rdate':
                    $propertyType = 'date-time';

                    foreach ($xmlProperty['value'] as $specialChild) {

                        $tagName = static::getTagName($specialChild['name']);

                        if ('period' === $tagName) {

                            $propertyParameters['value'] = 'PERIOD';
                            $propertyValue[]             = implode('/', $specialChild['value']);

                        }
                        else {
                            $propertyValue[] = $specialChild['value'];
                        }
                    }
                    break;

                default:
                    $propertyType  = static::getTagName($xmlProperty['value'][0]['name']);
                    $propertyValue = [$xmlProperty['value'][0]['value']];

                    if ('date' === $propertyType) {
                        $propertyParameters['value'] = 'DATE';
                    }
                    break;
            }

            $property = $this->root->createProperty(
                $propertyName,
                null,
                $propertyParameters,
                $propertyType
            );
            $parentComponent->add($property);
            $property->setXmlValue($propertyValue);
        }

    }

    protected function parseComponent(Component $parentComponent) {

        $components = $this->pointer['value'] ?: [];

        foreach ($components as $component) {

            $componentName    = static::getTagName($component['name']);
            $currentComponent = $this->root->createComponent(
                $componentName,
                null,
                false
            );

            $this->pointer = &$component;
            $this->parseVCalendarComponents($currentComponent);

            $parentComponent->add($currentComponent);

        }

    }

    /**
     * Sets the input data.
     *
     * @param resource|string $input
     * @return void
     */
    public function setInput($input) {

        if (is_resource($input)) {
            $input = stream_get_contents($input);
        }

        if (is_string($input)) {

            $reader = new SabreXml\Reader();
            $reader->elementMap['{' . self::XCAL_NAMESPACE . '}period']
                = 'Sabre\VObject\Parser\XML\Element\KeyValue';
            $reader->elementMap['{' . self::XCAL_NAMESPACE . '}recur']
                = 'Sabre\VObject\Parser\XML\Element\KeyValue';
            $reader->xml($input);
            $input  = $reader->parse();

        }

        $this->input = $input;

    }

    /**
     * Get tag name from a Clark notation.
     *
     * @param string $clarkedTagName
     * @return string
     */
    static protected function getTagName($clarkedTagName) {

        list($namespace, $tagName) = SabreXml\Util::parseClarkNotation($clarkedTagName);
        return $tagName;

    }
}
