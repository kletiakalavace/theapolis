<?php

namespace Theaterjobs\MainBundle\Twig;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * Highlight ElasticSearch Results
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class PaginationResultsExtension extends \Twig_Extension
{

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructs a new instance of PaginationResultsExtension.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('knp_results', array($this, 'paginationResults')),
        );
    }

    /**
     *
     * @param SlidingPagination $result
     * @param $index
     * @return mixed
     * @internal param $filter
     */
    public function paginationResults(SlidingPagination $result, $index)
    {
        $paginationData = $result->getPaginationData();

        if ($paginationData['totalCount'] > 0) {
            echo $this->translator->trans('list.search.label.results', ['%firstItemNumber%' => $paginationData['firstItemNumber'], '%lastItemNumber%' => $paginationData['lastItemNumber'], '%totalCount%' => $paginationData['totalCount']]);
        } else {
            $listUrl = "<a class='go-back' href='$index'> " . $this->translator->trans('list.search.label.go.back.to.list') . "</a>";
            echo $this->translator->trans('list.search.label.noResults', ['%listUrl%' => $listUrl]);
        }
    }
}