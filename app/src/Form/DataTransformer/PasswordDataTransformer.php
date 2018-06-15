<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 13.06.18
 * Time: 12:36
 */

namespace Form\DataTransformer;

use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

/**
 * Class BooleanDataTransformer
 */
class PasswordDataTransformer implements DataTransformerInterface
{
    //todo użyć tego gdzieś albo wywalić
    private $encoder = null;

    /**
     * PasswordDataTransformer constructor.
     * @param BCryptPasswordEncoder $security
     */
    public function __construct(BCryptPasswordEncoder $encoder)
    {
        $this->encoder = $encoder;
    }


    public function transform($hashPassword)
    {
        return $hashPassword;
    }

    public function reverseTransform($password)
    {
        $hash = $this->encoder->encodePassword($password, '');

        return $hash;
    }
}
