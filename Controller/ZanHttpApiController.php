<?php


namespace Zan\HttpApiBundle\Controller;


use Doctrine\DBAL\Driver\AbstractDriverException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * todo: move some of these methods into the core zan controller
 */
class ZanHttpApiController extends Controller implements UnserializedResponseController
{
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * Uses the EntityManager to persist the given Entity (or group of Entities)
     * and immediately save to the database.
     *
     * @param object|array $data
     */
    protected function persistAndFlush($data)
    {
        $em = $this->getEm();

        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $entity) {
                $em->persist($entity);
            }
        } else {
            $em->persist($data);
        }

        $em->flush();
    }

    /**
     * Returns all parameters from the Request object.
     *
     * Parameters are fetched from the GET parameters, else the POST parameters.
     *
     * @return array
     */
    protected function getRequestParams()
    {
        $request = $this->get("request");

        if ($request->getMethod() == "GET") {
            return $request->query->all();
        }

        return $request->request->all();
    }

    /**
     * Check if a Request parameter was provided for the given parameter name.
     *
     * @param string $name  Request parameter name
     * @return bool
     */
    protected function hasRequestParam($name)
    {
        $params = $this->getRequestParams();

        return isset($params[$name]);
    }

    /**
     * Returns a single Request parameter
     *
     * @param string $name
     * @return null|mixed
     */
    protected function getRequestParam($name)
    {
        $params = $this->getRequestParams();
        if (isset($params[$name])) {
            return $params[$name];
        }

        return null;
    }

    /**
     * Returns a single Request parameter or throws an exception if it wasn't
     * found
     *
     * @param string $name
     * @return mixed
     */
    protected function getRequiredRequestParam($name)
    {
        $params = $this->getRequestParams();
        if (isset($params[$name])) {
            return $params[$name];
        }

        throw new \InvalidArgumentException(sprintf("'%s' is a required parameter", $name));
    }
}