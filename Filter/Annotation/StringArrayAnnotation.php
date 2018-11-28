<?php

namespace App\Filter\Annotation;

abstract class StringArrayAnnotation
{
    public $fields = [];

    /**
     * Constructor.
     *
     * @param array $data key-value for properties to be defined in this class
     *
     * @throws AnnotationException
     */
    public function __construct(array $data)
    {
        if (!isset($data['value']) || !is_array($data['value'])) {
            throw new AnnotationException('Options must be a array of strings.');
        }

        foreach ($data['value'] as $key => $value) {
            if (is_string($value)) {
                $this->fields[] = $value;
            } else {
                throw new AnnotationException('Options must be a array of strings.');
            }
        }
    }
}
