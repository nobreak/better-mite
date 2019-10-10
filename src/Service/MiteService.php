<?php


// src/Service/MiteService.php
namespace App\Service;


class MiteService
{
    private $miteApiKey = "b89193fcc0259bcb";
    private $miteHost = "https://exozet.mite.yo.lk";



    public function getMiteEntries($year, $month, $day)
    {
        $response = $this->requestMiteEntries($year, $month, $day);

        $obj = json_decode($response);

        return $obj;
    }

    public function addMiteEntry($newMiteEntry)
    {

        $json='{"date_at":"' .$newMiteEntry->getDate().'","minutes":' .$newMiteEntry->getMinutes(). ', "service_id": ' .$newMiteEntry->getService()->getId().', "project_id": ' .$newMiteEntry->getProject()->getId().', "note":"' .$newMiteEntry->getMessage().'"}';

        $response = $this->requestAddMiteEntry($json);

        $obj = json_decode($response);

        return $obj;
    }


    public function deleteMiteEntry($id)
    {
        $response = $this->requestDeleteMiteEntry($id);

        $obj = json_decode($response);

        return $obj;
    }

    public function getMiteProjects()
    {
        $response = $this->requestMiteProjects();

        $obj = json_decode($response);

        return $obj;
    }


    public function getMiteServices()
    {
        $response = $this->requestMiteServices();

        $obj = json_decode($response);
        return $obj;
    }


    private function requestDeleteMiteEntry($id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->miteHost ."/time_entries/".$id.".json",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "DELETE",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Content-Length: 0",
            "Content-Type: application/json",
            "Host: exozet.mite.yo.lk",
            "X-MiteApiKey: ".$this->miteApiKey,
            "cache-control: no-cache"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          echo $response;
        }
    }



    private function requestAddMiteEntry($entryJSON)
    {
        $curl = curl_init();

        $body = "{\n   \"time_entry\": ".$entryJSON."\n}";

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->miteHost ."/time_entries.json",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => array(
          "Content-Type: application/json",
          "X-MiteApiKey: ".$this->miteApiKey,
          "cache-control: no-cache"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        return $response;
      }
    }

    private function requestMiteEntries($year, $month, $day)
    {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->miteHost ."/daily/$year/$month/$day.json",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "Accept: */*",
          "Accept-Encoding: gzip, deflate",
          "Cache-Control: no-cache",
          "Connection: keep-alive",
          "Content-Type: application/json",
          "Host: exozet.mite.yo.lk",
          "X-MiteApiKey: ".$this->miteApiKey,
          "cache-control: no-cache"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);



      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        return $response;
      }
    }




    private function requestMiteProjects()
    {
        $curl = curl_init();

          curl_setopt_array($curl, array(
          CURLOPT_URL => $this->miteHost ."/projects.json",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Content-Type: application/json",
            "Host: exozet.mite.yo.lk",
            "X-MiteApiKey: ".$this->miteApiKey,
            "cache-control: no-cache"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          return "cURL Error #:" . $err;
        } else {
          return $response;
        }
    }

    private function requestMiteServices() 
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->miteHost ."/services.json",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Content-Type: application/json",
            "Host: exozet.mite.yo.lk",
            "X-MiteApiKey: ".$this->miteApiKey,
            "cache-control: no-cache"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          return "cURL Error #:" . $err;
        } else {
          return $response;
        }
    }
}