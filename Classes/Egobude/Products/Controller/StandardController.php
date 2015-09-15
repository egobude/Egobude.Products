<?php
namespace Egobude\Products\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Egobude.Products".      *
 *                                                                        *
 *                                                                        */

use Egobude\Products\Service\CatalogService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use TYPO3\Neos\Controller\Module\AbstractModuleController;

/**
 * StandardController
 *
 * @Flow\Scope("singleton")
 */
class StandardController extends AbstractModuleController {

	/**
	 * Inject NodeTypeManager
	 *
	 * @Flow\Inject
	 * @var NodeTypeManager
	 */
	protected $nodeTypeManager;

	/**
	 * @var CatalogService
	 */
	protected $catalogService;

	/**
	 * @return void
	 */
	public function indexAction() {

	}
}