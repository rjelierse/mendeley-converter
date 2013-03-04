<?php

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\File\UploadedFile;
use Mendeley\Library;

class AppKernel
{
    protected $twig;

    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(dirname(__FILE__) . '/../app/views');
        $this->twig = new \Twig_Environment($loader);
        $this->twig->addExtension(new \Twig_Extension_Core());
    }

    public function handle(Request $request)
    {
        $response = new Response();

        if ($request->isMethod('post')) {
            try {
                if (null !== $libraryFile = $request->files->get('library')) {
                    if ($libraryFile->isValid()) {
                        $library = $this->convertLibrary($libraryFile);
                        $response = $this->createFileResponse($library->getRecords());
                    }
                }
            }
            catch (Exception $e) {
                $content = $this->twig->render('base.html.twig', array(
                    'message' => $e->getMessage(),
                    'message_type' => 'error',
                    'trace' => debug_backtrace()
                ));
                $response->setStatusCode(500);
                $response->setContent($content);
            }
        }
        else {
            $content = $this->twig->render('upload.html.twig');
            $response->setContent($content);
        }

        return $response->prepare($request);
    }

    protected function convertLibrary(UploadedFile $file)
    {
        return Library::createFromFile($file->getPath());
    }

    protected function createFileResponse(array $rows = array())
    {
        $content = $this->twig->render('records.csv.twig', array(
            'headers' => array_keys($rows[0]),
            'rows' => $rows
        ));

        $response = new Response($content);
        $response->setPublic();
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->set('Content-Type', 'text/plain', true);
        $response->headers->set('Content-Length', strlen($content), true);
        $response->headers->set('Content-Disposition', 'attachment, filename="library.csv"');

        return $response;
    }
}
