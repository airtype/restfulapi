<?php
namespace RestApi\Pagination;

use League\Fractal\Pagination\PaginatorInterface;
use Craft\ElementCriteriaModel;

class CraftPaginateVariableAdapter implements PaginatorInterface
{
    /**
     * Criteria
     *
     * @var Craft\ElementCriteriaModel
     */
    protected $criteria;

    protected $limit;

    protected $current_page;

    protected $last_page;

    protected $total;

    protected $count;

    protected $per_page;

    /**
     * Constructor
     *
     * @param Criteria $criteria
     *
     * @return void
     */
    public function __construct(ElementCriteriaModel $criteria)
    {
        $this->limit = $criteria->limit;

        $this->count = $criteria->count();

        $this->total = ceil($criteria->total() / $this->limit);

        $this->current_page = isset($criteria->page) ? (int) $criteria->page : 1;

        if ($this->current_page > $this->total) {
            $this->current_page = $this->total;
        }

        $this->last_page = ceil($this->total / $criteria->limit);
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->last_page;
    }

    /**
     * Get the total.
     *
     * @return int
     */
    public function getTotal()
    {
        // return (int) $this->criteria->total() - (int) $this->criteria->offset;
        return $this->total;
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->limit;
    }

    /**
     * Get the url for the given page.
     *
     * @param int $page
     *
     * @return string
     */
    public function getUrl($page)
    {
        $page_trigger = \Craft\craft()->config->get('paginationParameter', 'restApi');

        return \Craft\UrlHelper::getUrl(\Craft\craft()->request->getPath(), array_merge(\Craft\craft()->request->getQuery(), [
            $page_trigger => $page
        ]));
    }
}
