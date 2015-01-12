<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject\Component,
    Sabre\VObject\Component\VCalendar,
    Sabre\VObject\Component\VCard,
    Sabre\XML as SabreXML;

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
     * Parse xCal or xCard.
     *
     * @param resource|string $input
     * @param int|null $options
     */
    public function parse ( $input = null, $options = null ) {

        if(!is_null($input))
            $this->setInput($input);

        if(is_null($this->input))
            throw new EofException('End of input stream, or no input supplied');

        if($this->input['name'] === '{' . self::XCAL_NAMESPACE . '}icalendar') {

            $this->root = new VCalendar([], false);
            $this->pointer = &$this->input['value'][0];
            $this->parseVcalendarComponents($this->root, $options);
        }
        else
            throw new \Exception('Arg');

        return $this->root;
    }

    /**
     * Parse a vCalendar.
     *
     * @param Sabre\VObject\Component $parentComponent
     * @param int|null $options
     */
    protected function parseVcalendarComponents ( Component $parentComponent, $options = null ) {

        foreach($this->pointer['value'] as $children) {

            switch(static::getTagName($children['name'])) {

                case 'properties':
                    $xmlProperties = $children['value'];

                    foreach($xmlProperties as $xmlProperty) {

                        $propertyName       = static::getTagName($xmlProperty['name']);
                        $propertyValue      = [];
                        $propertyParameters = [];
                        $propertyType       = 'text';

                        foreach($xmlProperty['value'] as $i => $xmlPropertyChild) {

                            if('parameters' !== static::getTagName($xmlPropertyChild['name']))
                                continue;

                            $xmlParameters = $xmlPropertyChild['value'];

                            foreach($xmlParameters as $xmlParameter)
                                $propertyParameters[static::getTagName($xmlParameter['name'])]
                                    = $xmlParameter['value'][0]['value'];

                            array_splice($xmlProperty['value'], $i, 1);
                        }

                        switch($propertyName) {

                            case 'geo':
                                $propertyType               = 'float';
                                $propertyValue['latitude']  = 0;
                                $propertyValue['longitude'] = 0;

                                foreach($xmlProperty['value'] as $xmlRequestChild) {

                                    $propertyValue[static::getTagName($xmlRequestChild['name'])]
                                        = $xmlRequestChild['value'];
                                }
                                break;

                            case 'request-status':
                                $propertyType  = 'text';

                                foreach($xmlProperty['value'] as $xmlRequestChild) {

                                    $propertyValue[static::getTagName($xmlRequestChild['name'])]
                                        = $xmlRequestChild['value'];
                                }
                                break;

                            case 'categories':
                            case 'resources':
                            case 'freebusy':
                            case 'exdate':
                            case 'rdate':
                                foreach($xmlProperty['value'] as $specialChild) {

                                    $propertyValue[static::getTagName($specialChild['name'])]
                                        = $specialChild['value'];
                                }
                                break;

                            default:
                                $propertyType  = static::getTagName($xmlProperty['value'][0]['name']);
                                $propertyValue = [$xmlProperty['value'][0]['value']];
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
                    break;

                case 'components':
                    $components = $children['value'] ?: [];

                    foreach($components as $component) {

                        $componentName    = static::getTagName($component['name']);
                        $currentComponent = $this->root->createComponent(
                            $componentName
                        );

                        $this->pointer = &$component;
                        $this->parseVcalendarComponents(
                            $currentComponent,
                            $options
                        );

                        $parentComponent->add($currentComponent);
                    }
                    break;

                default:
                    throw new \Exception('Oops');
            }
        }
    }

    /**
     * Sets the input data.
     *
     * @param resource|string $input
     * @return void
     */
    public function setInput($input) {

        if(is_resource($input))
            $input = stream_get_contents($input);

        if(is_string($input)) {

            $reader = new SabreXML\Reader();
            $reader->elementMap['{' . self::XCAL_NAMESPACE . '}period']
                = 'Sabre\VObject\Parser\XML\Element\KeyValue';
            $reader->elementMap['{' . self::XCAL_NAMESPACE . '}recur']
                = 'Sabre\VObject\Parser\XML\Element\KeyValue';
            $reader->xml($input);
            $input  = $reader->parse();
        }

        $this->input = $input;

        return;
    }

    /**
     * Get tag name from a Clark notation.
     *
     * @param string $clarkedTagName
     * @return string
     */
    static protected function getTagName ( $clarkedTagName ) {

        list($namespace, $tagName) = SabreXML\Util::parseClarkNotation($clarkedTagName);

        return $tagName;
    }
}
