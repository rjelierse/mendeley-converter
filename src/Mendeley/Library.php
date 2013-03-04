<?php

namespace Mendeley;

class Library
{
    /**
     * @var \SimpleXmlElement
     */
    protected $xmlSource;

    /**
     * @var Record[]
     */
    protected $records;

    public function __construct()
    {
        $this->records = array();
    }

    public static function createFromFile($path)
    {
        $file = new static;
        $file->loadFile($path);
        $file->parse();

        return $file;
    }

    public function loadFile($path)
    {
        $useInternalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);
        libxml_clear_errors();

        $dom = new \DOMDocument();
        $dom->validateOnParse = true;
        if (false === $dom->load($path, LIBXML_NONET | (defined(LIBXML_COMPACT) ? LIBXML_COMPACT : 0))) {
            throw new \DOMException(implode("\n", $this->getXmlErrors($useInternalErrors)));
        }
        $dom->normalizeDocument();

        libxml_use_internal_errors($useInternalErrors);
        libxml_disable_entity_loader($disableEntities);

        foreach ($dom->childNodes as $node) {
            if (XML_DOCUMENT_TYPE_NODE === $node->nodeType) {
                throw new \DOMException('Document types are not allowed.');
            }
        }

        $this->xmlSource = simplexml_import_dom($dom);
    }

    public function parse()
    {
        $this->parseLibrary();
    }

    public function getRecords()
    {
        return $this->records;
    }

    private function parseLibrary()
    {
        foreach ($this->xmlSource->xpath('/xml/records/record') as $record) {
            $this->records[] = $this->parseRecord($record);
        }
    }

    private function parseRecord(\SimpleXmlElement $xml)
    {
        $record = new Record();

        // Get the filename
        if (!empty($xml->urls->{'pdf-urls'}->url)) {
            $record->setDocumentName((string) $xml->urls->{'pdf-urls'}->url);
        }

        // Get the title
        if (!empty($xml->titles->title)) {
            $record->setTitle((string) $xml->titles->title);
        }

        // Get the authors
        if (!empty($xml->contributors->authors)) {
            foreach ($xml->contributors->authors->author as $author) {
                $record->addAuthor((string) $author);
            }
        }
        
        // Get the publishing year
        if (!empty($xml->dates->year)) {
            $record->setYear((string) $xml->dates->year);
        }
        
        // Get the publisher
        if (!empty($xml->publisher)) {
            $record->setPublisher((string) $xml->publisher);
        }
        
        // Get the page count
        if (!empty($xml->pages)) {
            $record->setPages((string) $xml->pages);
        }

        // Get DOI
        if (!empty($xml->{'electronic-resource-num'})) {
            $record->setDoi((string) $xml->{'electronic-resource-num'});
        }
        elseif (!empty($xml->urls->{'web-urls'}->url)) {
            $record->setDoi((string) $xml->urls->{'web-urls'}->url);
        }

        if (!empty($xml->isbn)) {
            $record->setIsbn((string) $xml->isbn);
        }

        return $record;
    }

    private function getXmlErrors($useInternalErrors)
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ? $error->file : 'n/a',
                $error->line,
                $error->column
            );
        }

        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);

        return $errors;
    }
}
