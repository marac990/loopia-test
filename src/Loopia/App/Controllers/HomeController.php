<?php
/**
 * Created by PhpStorm.
 * User: marko
 * Date: 1.3.19.
 * Time: 10.49
 */

namespace Loopia\App\Controllers;

use Http\HttpRequest;
use Http\HttpResponse;
use Loopia\App\Model\Movie;
use Loopia\App\Template\Renderer;

class HomeController
{
    protected $_request;
    protected $_response;
    protected $_renderer;
    protected $_movies;

    public function __construct(
        HttpRequest $request,
        HttpResponse $response,
        Renderer $renderer,
        Movie $movies
    )
    {
        $this->_request = $request;
        $this->_response = $response;
        $this->_renderer = $renderer;
        $this->_movies = $movies;
    }

    public function index()
    {
        $data = $this->_movies->formatTitlesWhichContainThe()->sortByTitle()->get();
        $html = $this->_renderer->render('index', array_merge($data));
        $this->_response->setContent($html);
    }
}