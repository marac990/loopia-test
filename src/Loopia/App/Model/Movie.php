<?php
/**
 * Created by PhpStorm.
 * User: marko
 * Date: 1.3.19.
 * Time: 16.16
 */

namespace Loopia\App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Loopia\App\Api\Client;

class Movie
{

    protected $_client;
    protected $_movies;

    public function __construct(
        Client $client
    )
    {
        $this->_client = $client;
        $response = $this->_client->send($this->_client->getRequest('items'));
        $this->_movies = json_decode($response->getBody()->getContents(), true);
    }

    public function sortByTitle()
    {
        usort($this->_movies, [$this, 'sort']);
        return $this;
    }

    public function get()
    {
        return ['items' => new ArrayCollection($this->_movies)];
    }

    private function sort($x, $y)
    {
        return strcasecmp($x['title'], $y['title']);
    }

    public function formatTitlesWhichContainThe()
    {
        foreach ($this->_movies as $id => $movie) {
            if ( !( strpos($movie['title'], 'The ') === 0 ) )
                continue;
            $secondPart = substr( $movie['title'], 4 );
            $this->_movies[$id]['title'] = $secondPart . ', The';
        }
        return $this;
    }
}

