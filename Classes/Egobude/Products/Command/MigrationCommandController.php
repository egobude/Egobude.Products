<?php
namespace Egobude\Products\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Egobude.Products".      *
 *                                                                        *
 *                                                                        */
use TYPO3\Faker\Faker;
use TYPO3\Faker\Lorem;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\TYPO3CR\Domain\Service\Context;
use TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;

/**
 * MigrationCommandController
 *
 * @Flow\Scope("singleton")
 */
class MigrationCommandController extends CommandController {

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @var Context
     */
    protected $context;

    /**
     * indexCommand
     *
     * @param int $products
     * @param int $categories
     * @return string
     */
    public function indexCommand($products, $categories) {
        $context = $this->contextFactory->create(array('workspaceName' => 'live'));
        $startingPointProducts = $context->getNode('/sites/creative/node-55d375033b6bd');
        $startingPointCategories = $context->getNode('/sites/creative/node-55d383a290371');

        $this->output->outputLine('<info>Import products</info>');
        $this->output->progressStart($products);
        foreach ($this->getProducts($products) as $product) {
            $this->importProduct($product, $startingPointProducts, $context);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        $this->output->outputLine('<info>Import categories</info>');
        $this->output->progressStart($categories);
        foreach ($this->getCategories($categories) as $category) {
            $this->importCategory($category, $startingPointCategories, $startingPointProducts, $context);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }

    /**
     * importCategory
     *
     * @param array $category
     * @param NodeInterface $startingPointCategories
     * @param NodeInterface $startingPointProducts
     * @param Context $context
     * @return NodeInterface
     */
    private function importCategory(array $category, NodeInterface $startingPointCategories, NodeInterface $startingPointProducts, Context $context) {
        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Egobude.Products:Category'));
        $nodeTemplate->setProperty('title', $category['title']);

        $randomProducts = $this->getRandomProducts($context, $startingPointProducts);
        if (!empty($randomProducts)) {
            $nodeTemplate->setProperty('products', $randomProducts);
        }

        $categoryNode = $startingPointCategories->createNodeFromTemplate($nodeTemplate);

        if (!empty($category['description'])) {
            $body = $category['description'];
            $mainContentNode = $categoryNode->getNode('main');
            $bodyTemplate = new NodeTemplate();
            $bodyTemplate->setNodeType($this->nodeTypeManager->getNodeType('TYPO3.Neos.NodeTypes:Text'));
            $bodyTemplate->setProperty('text', $body);
            $mainContentNode->createNodeFromTemplate($bodyTemplate);
        }
        return $categoryNode;
    }

    /**
     * importProduct
     *
     * @param array $product
     * @param NodeInterface $startingPointProducts
     * @param Context $context
     */
    private function importProduct(array $product, NodeInterface $startingPointProducts, Context $context) {
        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Egobude.Products:Product'));
        $nodeTemplate->setProperty('title', $product['title']);
        $nodeTemplate->setProperty('sku', $product['sku']);
        $nodeTemplate->setProperty('price', $product['price']);

        $similarProducts = $this->getRandomProducts($context, $startingPointProducts);
        if (!empty($similarProducts)) {
            $nodeTemplate->setProperty('similarProducts', $similarProducts);
        }

        $accessories = $this->getRandomProducts($context, $startingPointProducts);
        if (!empty($accessories)) {
            $nodeTemplate->setProperty('accessories', $accessories);
        }

        $productNode = $startingPointProducts->createNodeFromTemplate($nodeTemplate);

        if (!empty($product['description'])) {
            $description = $product['description'];
            $mainContentNode = $productNode->getNode('main');
            $bodyTemplate = new NodeTemplate();
            $bodyTemplate->setNodeType($this->nodeTypeManager->getNodeType('TYPO3.Neos.NodeTypes:Text'));
            $bodyTemplate->setProperty('text', $description);
            $mainContentNode->createNodeFromTemplate($bodyTemplate);
        }
    }

    /**
     * getRandomProducts
     *
     * @param Context $context
     * @param NodeInterface $startingPointProducts
     *
     * @return array
     */
    private function getRandomProducts(Context $context, NodeInterface $startingPointProducts) {
        $searchResult = array();
        $randomProducts = $this->nodeDataRepository->findByParentAndNodeTypeInContext($startingPointProducts->getPath(), 'Egobude.Products:Product', $context, TRUE);
        if (!empty($randomProducts)) {
            foreach ($randomProducts as $node) {
                /* @var $node \TYPO3\TYPO3CR\Domain\Model\NodeInterface */
                $searchResult[] = $node->getIdentifier();
            }
            shuffle($searchResult);
            $searchResult = array_slice($searchResult, mt_rand(1, count($searchResult)));
        }
        return $searchResult;
    }

    /**
     * getCategories
     *
     * @param int $categoriesCount
     * @return array
     */
    private function getCategories($categoriesCount) {
        $categories = array();
        for ($i = 0; $i < $categoriesCount; $i++) {
            $categories[] = $this->generateFakeCategory();
        }
        return $categories;
    }

    /**
     * getProducts
     *
     * @param int $productsCount
     * @return array
     */
    private function getProducts($productsCount) {
        $products = array();
        for ($i = 0; $i < $productsCount; $i++) {
            $products[] = $this->generateFakeProduct();
        }
        return $products;
    }

    /**
     * generateFakeCategory
     *
     * @return array
     */
    private function generateFakeCategory() {
        $category = array();
        $category['title'] = Lorem::sentence(4);
        $category['description'] = Lorem::paragraph(5);
        return $category;
    }

    /**
     * generateFakeProduct
     *
     * @return array
     */
    private function generateFakeProduct() {
        $product = array();
        $product['title'] = Lorem::sentence(4);
        $product['description'] = Lorem::paragraph(5);
        $product['sku'] = Faker::numerify('#########');
        $product['price'] = Faker::numerify('##.##');
        return $product;
    }
}