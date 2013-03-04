<?php

namespace Mendeley;

class Record
{
    /**
     * @var string Course ID on Blackboard
     */
    protected $courseId;

    /**
     * @var string Course name
     */
    protected $courseName;

    /**
     * @var string Internal file path
     */
    protected $documentName;

    /**
     * @var string Publication title
     */
    protected $title;

    /**
     * @var array Publication authors
     */
    protected $authors;

    /**
     * @var boolean Is the publication imported from an external source?
     */
    protected $imported;

    /**
     * @var boolean Is the author from the TU Delft?
     */
    protected $internal;

    /**
     * @var string The publisher of the publication
     */
    protected $publisher;

    /**
     * @var string The owner of the publication (who has the copyright)
     */
    protected $owner;

    /**
     * @var int The year the publication was published
     */
    protected $year;

    /**
     * @var int The publication's page count
     */
    protected $pages;

    /**
     * @var int The publication's word count
     */
    protected $words;

    /**
     * @var boolean Is the publisher the owner of the document?
     */
    protected $copyright;

    /**
     * @var boolean Is the publication licensed?
     */
    protected $licensed;

    /**
     * @var string The ISBN of the publication
     */
    protected $isbn;

    /**
     * @var string The DOI of the publication
     */
    protected $doi;

    /**
     * @var string The ISSN of the publication
     */
    protected $issn;

    public function __construct()
    {
        $this->authors = array();
    }

    /**
     * @param array $authors
     */
    public function setAuthors($authors)
    {
        $this->authors = $authors;
    }

    public function addAuthor($author)
    {
        $this->authors[] = $author;
    }

    public function getAuthors()
    {
        return $this->authors;
    }

    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    public function getCopyright()
    {
        return $this->copyright;
    }

    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;
    }

    public function getCourseId()
    {
        return $this->courseId;
    }

    public function setCourseName($courseName)
    {
        $this->courseName = $courseName;
    }

    public function getCourseName()
    {
        return $this->courseName;
    }

    public function setDocumentName($documentName)
    {
        $this->documentName = $documentName;
    }

    public function getDocumentName()
    {
        return $this->documentName;
    }

    public function setDoi($doi)
    {
        $this->doi = $doi;
    }

    public function getDoi()
    {
        return $this->doi;
    }

    public function setImported($imported)
    {
        $this->imported = $imported;
    }

    public function getImported()
    {
        return $this->imported;
    }

    public function setInternal($internal)
    {
        $this->internal = $internal;
    }

    public function getInternal()
    {
        return $this->internal;
    }

    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
    }

    public function getIsbn()
    {
        return $this->isbn;
    }

    public function setIssn($issn)
    {
        $this->issn = $issn;
    }

    public function getIssn()
    {
        return $this->issn;
    }

    public function setLicensed($licensed)
    {
        $this->licensed = $licensed;
    }

    public function getLicensed()
    {
        return $this->licensed;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setPages($pages)
    {
        if (is_string($pages)) {
            if (false === strpos($pages, '-')) {
                $this->pages = intval($pages);
            }
            else {
                list($begin, $end) = explode('-', $pages, 2);
                $this->pages = $end - $begin;
            }
        }
        else {
            $this->pages = $pages;
        }
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    public function getPublisher()
    {
        return $this->publisher;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setWords($words)
    {
        $this->words = $words;
    }

    public function getWords()
    {
        return $this->words;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function toArray()
    {
        return array(
            'Blackboard Course ID' => $this->courseId,
            'Coursenaam' => $this->courseName,
            'Documentnaam' => $this->documentName,
            'Titel' => $this->title,
            'Overname j/n' => $this->boolToText($this->imported),
            'Auteur' => implode(';', $this->authors),
            'Auteur TU Delft j/n' => $this->boolToText($this->internal),
            'Uitgever' => $this->publisher,
            'Rechthebbende' => $this->owner,
            'Jaar publicatie' => $this->year,
            'Aantal pagina\'s' => $this->pages,
            'Aantal woorden' => $this->words,
            'Auteursrecht uitgever j/n' => $this->boolToText($this->copyright),
            'Vrijstelling door licentie' => $this->boolToText($this->licensed),
            'ISBN' => $this->isbn,
            'DOI' => $this->doi,
            'ISSN' => $this->issn
        );
    }

    private function boolToText($value)
    {
        if ($value === null) {
            return '?';
        }
        if ($value === false) {
            return 'n';
        }

        return 'j';
    }
}
