<?php

declare(strict_types=1);

namespace Dune\Pine;

use Dune\Pine\Exception\ViewNotFound;
use Dune\Pine\Exception\LayoutNotFound;
use Dune\Pine\ViewInterface;
use Dune\Pine\ViewContainer;
use Dune\Pine\FileMapper;
use Dune\Pine\CaptureLayout;
use Dune\Pine\Engine\ProcceserEngine;

class View implements ViewInterface
{
    use ViewContainer;

    /**
     * The view file.
     *
     * @var string
     */
    private string $file;

    /**
     * store the data passing by the view.
     *
     * @var array<string,mixed>
     */
    private array $var = [];

    /**
     * \Dune\Pine\Engine\ProcceserEngine instance
     *
     * @var ProcceserEngine
     */
    private ?ProcceserEngine $engine = null;
    /**
     * \Dune\Pine\FileMapper instance
     *
     * @var FileMapper
     */
    public FileMapper $mapper;
    /**
     * \Dune\Pine\CaptureLayout instance
     *
     * @var CaptureLayout
     */
    public CaptureLayout $capture;

    /**
     * @param FileMapper $mapper
     * @param CaptureLayout $capture
     * @param ProcceserEngine $engine
     */
    public function __construct(FileMapper $mapper, CaptureLayout $capture, ProcceserEngine $engine)
    {
        $this->mapper = $mapper;
        $this->capture = $capture;
        $this->engine = $engine;
    }

    /**
     * @param  string  $view
     * @param  array<string,mixed>  $data
     *
     * @throw \Dune\Pine\Exception\ViewNotFound
     *
     * @return string|null
     */
    public function render(string $view, array $data = []): ?string
    {
        $this->file = $this->mapper->getPineFile($view);
        $this->var = $data;
        if($this->mapper->cacheMode()) {
            return $this->engine->load($this->file, $data);
        }
        return $this->loadFile();
    }
    /**
     * compile the layout file if exists
     *
     * @return mixed
     */
    private function loadFile(): mixed
    {
        $template = $this->mapper->getContents($this->file);
        return $this->renderFiles($template);
    }
    /**
     * render the layout and view files
     *
     * @param string $template
     *
     * @return mixed
     */
    private function renderFiles(string $template): mixed
    {
        return $this->engine->load($template, $this->var);
    }
    /**
     * get cached template contents from its key
     *
     * @param string $key
     *
     * @return string
     */
    public function getCacheContent(string $key): string
    {
        $file = $this->mapper->getCacheFile($key);
        return $this->mapper->getContents($file);
    }
}
