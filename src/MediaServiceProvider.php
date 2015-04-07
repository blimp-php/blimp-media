<?php
namespace Blimp\Media;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

use Blimp\Media\Documents\Media;

class MediaServiceProvider implements ServiceProviderInterface {
    public function register(Container $api) {
        $api['media.http.path'] = '';
        $api['media.physical.path'] = __DIR__;

        $api['media.store'] = $api->protect(function ($uploadedFile, $bucket, $filename = null, $extension = null) use($api) {
            if(empty($filename)) {
                $filename = sha1(uniqid(mt_rand(), true));
            }

            if(empty($extension)) {
                $extension = $uploadedFile->guessExtension();

                if(empty($extension)) {
                    $extension = 'bin';
                }
            }

            $file = $uploadedFile->move($api['media.physical.path'] . '/' . $bucket, $filename . '.' . $extension);

            $dm = $api['dataaccess.mongoodm.documentmanager']();

            $media = new Media();
            $media->setBasePath($api['media.physical.path']);
            $media->setBucket($bucket);
            $media->setFilePath($filename . '.' . $extension);

            $dm->persist($media);

            return $media;
        });

        $api->extend('blimp.extend', function ($status, $api) {
            if($status) {
                if ($api->offsetExists('dataaccess.mongoodm.mappings')) {
                    $api->extend('dataaccess.mongoodm.mappings', function ($mappings, $api) {
                        $mappings[] = ['dir' => __DIR__ . '/Documents', 'prefix' => 'Blimp\\Media\\Documents\\'];

                        return $mappings;
                    });
                }
            }

            return $status;
        });
    }
}
