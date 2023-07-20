<?php

/**
 * This file is part of the Astra Security Suite.
 *
 *  Copyright (c) 2019 (https://www.getastra.com/)
 *
 *  For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
/**
 * @author HumansofAstra-WZ <help@getastra.com>
 * @date   2019-03-23
 */
namespace AstraPrefixed\GetAstra\Plugins\Scanner\Helpers;

use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Signature;
class ScanRelayHelper extends RelayHelper
{
    public function getChecksums($cms, $version, $locale = 'en_US')
    {
        $client = $this->getClient();
        $client->setHeader('Accept', 'application/ld+json');
        $response = $client->get('/api/waf/plugins/scanner/checksums', ['cms' => $cms, 'version' => $version, 'locale' => $locale, 'itemsPerPage' => 1]);
        $response = $this->checkResponseStatusCode($client, 200, ['a' => 'b']);
        return $response;
    }
    public function getSignatures()
    {
        $client = $this->getClient();
        $client->setHeader('Accept', 'application/json');
        $response = $client->get('/api/waf/plugins/scanner/signatures?pagination=false');
        $response = $this->checkResponseStatusCode($client, 200);
        return $response;
    }
    public function addIssue($data)
    {
        //$data['site'] = ConfigHelper::get('siteIri');
        $data['scan'] = ConfigHelper::get('scanCode');
        $data['data'] = \json_decode($data['data']);
        $client = $this->getClient();
        $client->setHeader('Accept', 'application/json');
        $client->setHeader('Content-Type', 'application/json');
        $response = $client->post('/api/waf/plugins/scanner/issues', $data);
        $log = $client->response;
        //StatusHelper::add('4', 'relay', 'Received response code: ' . json_encode($log));
        $response = $this->checkResponseStatusCode($client, 201);
        return $response;
    }
    public function updateScanStatus($data)
    {
    }
    public function sendMetrics()
    {
        $client = $this->getClient();
        $client->setHeader('Accept', 'application/json');
        $client->setHeader('Content-Type', 'application/merge-patch+json');
        $client->setHeader('X-HTTP-METHOD-OVERRIDE', 'PATCH');
        $data = ['state' => 'complete', 'patch' => ['statusDesc' => "Scan is completed", 'duration' => ConfigHelper::get('scanDuration', 2), 'fileCount' => ConfigHelper::get('totalFilesScanned', 0), 'metrics' => ['totalFiles' => ConfigHelper::get('totalFiles', 0), 'remainingFiles' => ConfigHelper::get('remainingFiles', 0), 'scanningRate' => ConfigHelper::get('fileRate', 0)], 'signaturesCount' => \count(Signature::query()->get())]];
        $scanUrl = ConfigHelper::get('scanCode', '0', \false);
        \AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\StatusHelper::add('4', 'relay', $scanUrl);
        $response = $client->post($scanUrl . '/state', $data);
    }
    public function sendScanStatus($status = 'run', $message = 'Scan is running')
    {
        $client = $this->getClient();
        $client->setHeader('Accept', 'application/json');
        $client->setHeader('Content-Type', 'application/merge-patch+json');
        $client->setHeader('X-HTTP-METHOD-OVERRIDE', 'PATCH');
        $startTime = (int) ConfigHelper::get('startTime');
        $scanDuration = (\time() - $startTime) / 60;
        $localScanState = ConfigHelper::get('scanState', 'unknown');
        ConfigHelper::set('scanState', $status);
        $data = ['state' => $status, 'patch' => [
            'statusDesc' => \substr($message, 0, 50000),
            //limiting status Desc to 50K characters
            'duration' => $scanDuration,
            'fileCount' => ConfigHelper::get('totalFilesScanned', 0),
            'metrics' => ['totalFiles' => ConfigHelper::get('totalFiles', 0), 'remainingFiles' => ConfigHelper::get('remainingFiles', 0), 'scanningRate' => ConfigHelper::get('fileRate', 0)],
            'signaturesCount' => \count(Signature::query()->get()),
        ]];
        $scanUrl = ConfigHelper::get('scanCode', '0', \false);
        if ($status !== $localScanState) {
            // State is changing, to let us make the transition
            $client->post($scanUrl . '/state', $data);
        } else {
            // Since the stats is the same, just patch the data
            $client->post($scanUrl, $data['patch']);
        }
        $log = $client->response;
        StatusHelper::add('4', 'relay', 'sendScanStatus: ' . \json_encode($log));
    }
    public function sendCmsDetails($cms, $version, $locale = 'en_US')
    {
        $client = $this->getClient();
        $client->setHeader('Accept', 'application/json');
        $client->setHeader('Content-Type', 'application/merge-patch+json');
        $client->setHeader('X-HTTP-METHOD-OVERRIDE', 'PATCH');
        $data = ['cms' => ['cms' => $cms, 'version' => $version, 'locale' => $locale]];
        $scanUrl = ConfigHelper::get('scanCode', '0', \false);
        $response = $client->post($scanUrl, $data);
        $log = $client->response;
        StatusHelper::add('4', 'relay', 'sendCmsDetails: ' . \json_encode($log));
    }
    /**
     * This function triggers a bounce request -> it gets the astra server to trigger a scan fork if self-requests are
     * blocked on a client.
     *
     * @param $cronKey
     * @param $isFork
     * @param $scanUrl
     */
    public function sendBounceRequest($cronKey, $isFork, $scanUrl)
    {
        $client = $this->getClient();
        $client->setHeader('Accept', 'application/json');
        $client->setHeader('Content-Type', 'application/json');
        $client->setTimeout(3);
        $isFork = $isFork ? '1' : '0';
        $scanUrl .= '/bounce';
        $data = ['cronKey' => $cronKey, 'isFork' => $isFork];
        $client->post($scanUrl, $data);
        $log = $client->response;
        StatusHelper::add('4', 'relay', 'sendBounceRequest: ' . \json_encode($log));
    }
}
