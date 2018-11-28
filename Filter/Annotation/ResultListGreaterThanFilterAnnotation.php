<?php

namespace App\Filter\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class ResultListGreaterThanFilterAnnotation extends StringArrayAnnotation
{
    /**
     * Constructor.
     *
     * @param array $data key-value for properties to be defined in this class
     *
     * @throws \App\Filter\AnnotationException
     * @throws \App\Filter\Annotation\AnnotationException
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
