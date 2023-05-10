<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Finder\Finder;
use App\Form\CsvFileType;
use App\Service\BulkInsertService;

class CSVController extends AbstractController
{
    private $bulkInsertService;

    public function __construct(BulkInsertService $bulkInsertService) {
        $this->bulkInsertService = $bulkInsertService;
    }

    #[Route('/app/csv')]
    public function import(Request $request)
    {
        $form = $this->createForm(CsvFileType::class);

        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csv_file')->getData();
            // $this->generateCSV();
            $csvData = $this->parseCsv($csvFile);
            $uniqueUrls = $this->getUniqueUrls($csvData);
            
            $mappedUrls = array_map(function ($url) {
                return [
                    'url' => $url,
                ];
            }, $uniqueUrls);
            $newlyAddedCount = $this->bulkInsertService->bulkInsert($mappedUrls);
            return $this->redirectToRoute('app_csv_importfinished', array('count' => $newlyAddedCount));
        }
    
        return $this->render('csv.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/app/csv/finished/{count}')]
    public function importFinished($count, Request $request)
    {
        return $this->render('csv_finished.html.twig', [
            'newlyAddedCount' =>  $count,
        ]);
    }

    private function parseCsv(UploadedFile $csvFile): array {
        $ignoreFirstLine = false;

        $i = 0;
        $csvData = [];
        $file = fopen($csvFile->getPathname(), 'r');
        while (($data = fgetcsv($file)) !== false) {
            $i++;
            if ($ignoreFirstLine && $i == 1) {continue;}
            $csvData[] = $data[0];
        }
        fclose($file);
        return $csvData;
    }

    private function getUniqueUrls($csvData): array {
        $urlMap = array();
        foreach ($csvData as $row) {
            $url = trim($row);
            $parsedUrl = parse_url($url);
            
            // Ignore URLs without a host or path
            if (!isset($parsedUrl['host']) || !isset($parsedUrl['path'])) {
                continue;
            }
            // Remove default port 80 and empty query parameters
            if (isset($parsedUrl['port']) && $parsedUrl['port'] == 80) {
                unset($parsedUrl['port']);
            }
            if (isset($parsedUrl['query']) && empty($parsedUrl['query'])) {
                unset($parsedUrl['query']);
            }
        
            // Sort query parameters by name and value
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $queryParams);
                ksort($queryParams);
                $parsedUrl['query'] = http_build_query($queryParams);
            }
        
            // Build a unique key for the URL
            $urlKey = $parsedUrl['host'] . $parsedUrl['path'];
            if (isset($parsedUrl['query'])) {
                $urlKey .= '?' . $parsedUrl['query'];
            }
        
            // Store the URL in the map with the original URL as the value
            if (!isset($urlMap[$urlKey])) {
                $urlMap[$urlKey] = $url;
            }
        }
        
        // Get the unique URLs from the map
        $uniqueUrls = array_values($urlMap);
        return $uniqueUrls;
    }
    
    // If you don't have CSV file to test,
    // This is just to generate sample CSV file under /public/urls.csv 
    // contains 100000 urls

    private function generateCSV() {
        $urlCount = 100000; // number of URLs to generate
        $csvFile = 'urls.csv'; // name of the CSV file to create

        // generate URLs
        $urls = [];
        for ($i = 0; $i < $urlCount; $i++) {
            $path = '/path' . $i + 100001;
            $query = 'param1=value1&param2=value2';
            $url = 'http://example.com' . $path . '?' . $query;
            $urls[] = $url;
        }

        // write URLs to CSV file
        $fp = fopen($csvFile, 'w');
        foreach ($urls as $url) {
            fputcsv($fp, [$url]);
        }
        fclose($fp);
    }
}