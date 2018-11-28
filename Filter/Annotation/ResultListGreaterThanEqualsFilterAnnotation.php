<?php

namespace App\Filter\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class ResultListGreaterThanEqualsFilterAnnotation extends StringArrayAnnotation
{
    /**
     * Constructor.
     *
     * @param array $data key-value for properties to be defined in this class
     R
     * @throws \App\Filter\AnnotationException
     * @throws \App\Filter\Annotation\AnnotationException
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
