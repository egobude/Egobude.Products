<?php
namespace Egobude\Products\Service;
/*                                                                         *
 * This script belongs to the package "Egobude.Products".           *
 *                                                                         *
 * It is free software; you can redistribute it and/or modify it under     *
 * the terms of the GNU Lesser General Public License, either version 3    *
 * of the License, or (at your option) any later version.                  *
 *                                                                         */
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository;

/**
 * CatalogService
 *
 * @Flow\Scope("singleton")
 */
class CatalogService {

    /**
     * Inject NodeDataRepository
     *
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * Returns node by the specified node type
     *
     * @param string $nodeType Type of node to be searched.
     * @param array  $ignoreNodes Array of node paths which need to be ignored.
     * @return \TYPO3\Flow\Persistence\QueryResultInterface
     */
    public function findByNodeTypeAndCurrentSite($nodeType, $ignoreNodes = NULL) {
        if ($ignoreNodes === NULL) {
            $ignoreNodes[] = '';
        }
        $query = $this->nodeDataRepository->createQuery();
        $constraints = array(
            $query->equals('nodeType', $nodeType),
            $query->logicalNot($query->in('path', $ignoreNodes))
        );
        return $query->matching($query->logicalAnd($constraints))->execute();
    }

}