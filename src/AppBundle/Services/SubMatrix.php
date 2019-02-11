<?php

namespace AppBundle;

/**
 * This class allows the use of customized substitution matrices.  See tech doc for details.
 */
class SubMatrix
{
    var $rules;

    /**
     * submatrix simply initializes the rules property to the empty array.
     */
    function submatrix()
    {
	$this->rules = array();
    }

    /**
     * addrule() adds a rule to the substitution matrix.
     * @param type $x
     */
    function addrule($x)
    {
	$x = func_get_args();
	array_push($this->rules, $x);
    }
}
