<?php

namespace Yoyo\Plugins\YoyoDemoWidget;

defined('ABSPATH') or die;

use Clickfwd\Yoyo\Component;

class Posts extends Component
{
	public $pg = 1;

	public $limit = 10;

	protected function getPostsProperty()
	{
		$args = [
			'posts_per_page' => $this->limit,
			// 'category_name' => $btmetanm,
			'paged' => $this->pg,
			'post_type' => 'post',
			'orderby' => 'date',
			'order' => 'DESC',
		];

		$items = new \WP_Query( $args );

		return $items->posts;
	}

    protected function getStartProperty()
    {
        return 1 + (($this->pg - 1) * $this->limit);
    }

    protected function getNextProperty()
    {
        return $this->pg + 1;
    }

    protected function getPreviousProperty()
    {
        return $this->pg > 1 ? $this->pg - 1 : false;
    }	
}