<?php

namespace App\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WebController extends AbstractController
{
    /**
     * @Route("/web", name="app_web")
     */
    public function interactWithWebsite()
    {
        $url = 'https://www2.agenciatributaria.gob.es/wlpl/inwinvoc/es.aeat.dit.adu.eeca.catalogo.vis.VisualizaSc?COMPLETA=NO&ORIGEN=J';
        $verificationCode = 'ZGUG4NZVWJUSMNTV';

        $client = HttpClient::create();
        $response = $client->request('GET', $url);
        $html = $response->getContent();

        // Crear un objeto Crawler a partir del HTML
        $crawler = new Crawler($html);

        // Encontrar el formulario por su ID
        $form = $crawler->filter('form#formu')->form();

        // Establecer el valor del campo "CSV"
        $form['CSV'] = $verificationCode;

        // Obtener la URL de acción del formulario
        $actionUrl = $form->getUri();

        // Enviar el formulario
        $response = $client->request('POST', $actionUrl, [
            'body' => $form->getValues(),
        ]);
        
        $client = HttpClient::create();

        $iframeUrl = $url = 'https://www2.agenciatributaria.gob.es/wlpl/inwinvoc/es.aeat.dit.adu.eeca.catalogo.vis.VisualizaSc?COMPLETA=SI&ORIGEN=D&CLAVE_CAT=&NIF=&ANAGRAMA=&CSV=' . $verificationCode . '&CLAVE_EE=&PAGE=&SEARCH=';

        // Realizar una solicitud GET al URL del iframe        
        $response = $client->request('GET', $iframeUrl);
        
        // Obtener el contenido del archivo
        $fileContent = $response->getContent();

        $filePath = '/app/public/ficheros/descargado.pdf';
        $directoryPath = '/app/public/ficheros/';
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        $filePath = $directoryPath . 'descargado.pdf';

        file_put_contents($filePath, $fileContent);



        // Obtener el contenido de la respuesta
        $resultHtml = $response->getContent();

        return $this->json('Archivo descargado exitosamente');
    }
}
