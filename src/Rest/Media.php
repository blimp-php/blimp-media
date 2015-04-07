<?php
namespace Blimp\Media\Rest;

use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Blimp\Http\BlimpHttpException;

use Blimp\DataAccess\Rest\MongoODMResource;

class Media {
    public function process(Container $api, Request $request, $id, $_bucket, $_securityDomain = null) {
        switch ($request->getMethod()) {
            case 'GET':
                $token = $api['security']->getToken();
                $user = $token !== null ? $token->getUser() : null;

                $contentLang = $api['http.utils']->guessContentLang($request->getLanguages());

                $result = $api['dataaccess.mongoodm.utils']->get('\Blimp\Media\Documents\Media', $id, $contentLang, $_securityDomain, $user);

                $b = $result->getBucket();
                if($b === $_bucket) {
                    $file = $result->getFilePath();

                    if(filter_var($file, FILTER_VALIDATE_URL)) {
                        return new RedirectResponse($file);
                    } else if(file_exists($api['media.physical.path'].'/'.$b.'/'.$file)) {
                        return new BinaryFileResponse($result->getBasePath().'/'.$result->getBucket().'/'.$file);
                    } else if(file_exists($api['media.physical.path'].'/'.$b.'/'.$file)) {
                        return new BinaryFileResponse($api['media.physical.path'].'/'.$result->getBucket().'/'.$file);
                    }
                }

                throw new BlimpHttpException(Response::HTTP_NOT_FOUND, "Not found");

                break;

            default:
                throw new BlimpHttpException(Response::HTTP_METHOD_NOT_ALLOWED, "Method not allowed");
        }
    }
}
