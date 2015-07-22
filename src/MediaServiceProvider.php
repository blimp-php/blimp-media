<?php

namespace Blimp\Media;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Blimp\Media\Documents\Media;

class MediaServiceProvider implements ServiceProviderInterface
{
    public function register(Container $api)
    {
        $api['media.store'] = $api->protect(function ($uploadedFile, $bucket, $filename = null, $extension = null) use ($api) {
            if (empty($filename)) {
                $filename = sha1(uniqid(mt_rand(), true));
            }

            if (empty($extension)) {
                $extension = $uploadedFile->guessExtension();

                if (empty($extension)) {
                    $extension = 'bin';
                }
            }

            $stream = fopen($uploadedFile->getRealPath(), 'r+');
            $api['media.filesystems']['user']->writeStream($bucket.'/'.$filename.'.'.$extension, $stream, [
                'visibility' => 'public',
                'mimetype' => $uploadedFile->getMimeType(),
            ]);
            fclose($stream);

            return [
                'bucket' => $bucket,
                'file' => $filename.'.'.$extension,
                'mime' => $uploadedFile->getMimeType(),
                'originalName' => $uploadedFile->getClientOriginalName()
            ];
        });
    }
}
