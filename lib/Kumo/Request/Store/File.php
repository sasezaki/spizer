<?php
require_once 'Kumo/Request/Store.php';
class Kumo_Request_Store_File extends Kumo_Request_Store
{
    private $fp;
    private $fname;
    private $line;
    private $lnum = 0;

    public function serialize()
    {
        if (is_resource($this->fp)) {
            $this->line = ftell($this->fp);
            fclose($this->fp);
        }
        return serialize(array($this->fp, $this->fname, $this->line));
    }

    public function unserialize($serialized)
    {
        list($this->fp, $this->fname, $this->line) = unserialize($serialized);
        $this->open();
    }

    protected function init()
    {
        $this->fname = $this->getConfig();
        $this->open();
    }

    public function rewind()
    {
        rewind($this->fp);
        $this->line = fgets($this->fp);
    }

    public function open()
    {
        $this->fp = fopen($this->fname, 'r');
    }

    public function current()
    {
        return ($this->line) ? unserialize($this->line): '';
    }

    public function next()
    {
        $this->line = fgets($this->fp);
        $this->lnum++;
    }

    public function key()
    {
        return $this->lnum;
    }

    public function valid()
    {
        return !feof($this->fp);
    }

    public function add($request)
    {
        file_put_contents($this->fname, serialize($request)."\n", FILE_APPEND);
    }
}
