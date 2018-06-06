<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 06.06.18
 * Time: 17:39
 */

namespace Form;

use Repositiory\TagRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class TagDataTransformer
 */
class TagDataTransformer implements DataTransformerInterface
{
    protected $tagRepository = null;

    /**
     * TagDataTransformer constructor.
     *
     * @param null $tagRepository
     */
    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Function runed filling form witch data
     *
     * @param mixed $tags
     *
     * @return mixed|string
     */
    public function transform($tags)
    {
        if (null === $tags) {
            return '';
        }

        return implode(',', $tags);
    }

    /**
     * Function runed at form submmit
     *
     * @param mixed $string
     *
     * @return array|mixed
     */
    public function reverseTransform($string)
    {
        $tagNames = explode(',', $string);
        $tags = [];

        foreach ($tagNames as $tagName) {
            if ('' !== trim($tagName)) {
                $tag = $this->tagRepository->findOneByName($tagName);
                if (null === $tag || !count($tag)) {
                    $tag = [];
                    $tag['name'] = $tagName;
                    $tag = $this->tagRepository->save($tag);
                }
                $tags[] = $tag;
            }
        }

        return $tags;
    }
}
