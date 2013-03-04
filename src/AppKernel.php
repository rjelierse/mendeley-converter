<?php

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\File\File;
use Monolog\Logger,
    Monolog\Handler\StreamHandler;
use Mendeley\Library;

class AppKernel
{
    protected $twig;

    protected $logger;

    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(dirname(__FILE__) . '/../app/views');
        $this->twig = new \Twig_Environment($loader);
        $this->twig->addExtension(new \Twig_Extension_Core());

        $this->logger = new Logger('app');
        $this->logger->pushHandler(new StreamHandler(dirname(__FILE__) . '/../app/mendeley.log'));
    }

    public function handle(Request $request)
    {
        $response = new Response();

        if ($request->isMethod('post')) {
            try {
                if (null !== $uploadedFile = $request->files->get('library')) {
                    /** @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile */
                    if ($uploadedFile->isValid()) {
                        $filename = uniqid('mendeley');
                        $libraryFile = $uploadedFile->move('/tmp', $filename);
                        $library = $this->convertLibrary($libraryFile);
                        $response = $this->createFileResponse($library->getRecords());
                        @unlink($libraryFile->getPathname());
                    }
                }
            }
            catch (Exception $e) {
                $content = $this->twig->render('base.html.twig', array(
                    'message' => get_class($e).': '.$e->getMessage(),
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

    protected function convertLibrary(File $file)
    {
        return Library::createFromFile($file->getPathname());
    }

    protected function createFileResponse(array $rows = array())
    {
        $content = $this->twig->render('records.csv.twig', array(
            'headers' => array_keys($rows[0]),
            'rows' => $rows
        ));

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="library.csv"');

        return $response;
    }
}
