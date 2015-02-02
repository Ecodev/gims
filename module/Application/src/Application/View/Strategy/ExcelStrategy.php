<?php

namespace Application\View\Strategy;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\ViewEvent;

class ExcelStrategy extends \Zend\View\Strategy\PhpRendererStrategy
{

    /**
     * Constructor
     *
     * @param  PhpRenderer $renderer
     */
    public function __construct(PhpRenderer $renderer)
    {
        parent::__construct($renderer);
    }

    /**
     * Select the PhpRenderer; typically, this will be registered last or at
     * low priority.
     *
     * @param  ViewEvent $e
     * @return PhpRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        if ($e->getModel() instanceof \Application\View\Model\ExcelModel) {

            // Look for .excel.php files instead of standard .phtml files
            foreach ($this->renderer->resolver() as $r) {
                if ($r instanceof \Zend\View\Resolver\TemplatePathStack) {
                    $r->setDefaultSuffix('.excel.php');
                }
            }

            return $this->renderer;
        }
    }

    /**
     * Populate the response object from the View
     *
     * Populates the content of the response object from the view rendering
     * results.
     *
     * @param ViewEvent $e
     * @return void
     */
    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            return;
        }

        $result = $e->getResult();
        $response = $e->getResponse();

        // Set content
        // If content is empty, check common placeholders to determine if they are
        // populated, and set the content from them.
        if (empty($result)) {
            $placeholders = $renderer->plugin('placeholder');
            foreach ($this->contentPlaceholders as $placeholder) {
                if ($placeholders->containerExists($placeholder)) {
                    $result = (string) $placeholders->getContainer($placeholder);
                    break;
                }
            }
        }
        $response->setContent($result);
    }
}
