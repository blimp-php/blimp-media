<?php
namespace Blimp\Media\Rest;

use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Blimp\Http\BlimpHttpException;

use Blimp\DataAccess\Rest\MongoODMResource;

class Media extends MongoODMResource {
    public function process(Container $api, Request $request, $id, $_securityDomain = null) {
        switch ($request->getMethod()) {
            case 'GET':
                $result = super::process($api, $request, $id, $_securityDomain);

                $file = $result->getFile();
                if(filter_var($file, FILTER_VALIDATE_URL)) {
                    return new RedirectResponse($file);
                } else if(file_exists($api['media.physical.path'].$file)) {
                    return new BinaryFileResponse($api['media.physical.path'].$file);
                }

                throw new BlimpHttpException(Response::HTTP_NOT_FOUND, "Not found");

                break;

            default:
                throw new BlimpHttpException(Response::HTTP_METHOD_NOT_ALLOWED, "Method not allowed");
        }
    }
}
