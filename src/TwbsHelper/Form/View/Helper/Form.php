<?php

namespace TwbsHelper\Form\View\Helper;

class Form extends \Zend\Form\View\Helper\Form
{
    use \TwbsHelper\View\Helper\HtmlTrait;

    const LAYOUT_HORIZONTAL = 'horizontal';
    const LAYOUT_INLINE     = 'inline';

    /**
     * @param \Zend\Form\FormInterface $oForm
     * @return \TwbsHelper\Form\View\Helper\Form|string
     */
    public function __invoke(\Zend\Form\FormInterface $oForm = null)
    {
        if ($oForm) {
            return $this->render($oForm);
        }
        return $this;
    }

    /**
     * Render a form from the provided $oForm,
     *
     * @param \Zend\Form\FormInterface $oForm
     * @return string
     */
    public function render(\Zend\Form\FormInterface $oForm): string
    {
        // Prepare form if needed
        if (method_exists($oForm, 'prepare')) {
            $oForm->prepare();
        }

        // Set form role
        if (!$oForm->getAttribute('role')) {
            $oForm->setAttribute('role', 'form');
        }

        $sFormLayout = $oForm->getOption('layout');

        // Set inline class
        if ($sFormLayout === self::LAYOUT_INLINE) {
            $this->setClassesToElement($oForm, ['form-inline']);
        }

        $sElementsContent = $this->renderElements($oForm, $sFormLayout);

        return $this->openTag($oForm)
            . ($sElementsContent ? $this->addProperIndentation($sElementsContent, true) : '') .
            $this->closeTag();
    }


    /**
     * @param \Zend\Form\FormInterface $oForm
     * @return string
     */
    protected function renderElements(\Zend\Form\FormInterface $oForm): string
    {
        // Store button groups
        $aButtonGroups = [];

        // Store button groups column from buttons
        $aButtonGroupsColumns = [];

        // Store elements rendering
        $aElementsRendering = [];

        // Retrieve view helper plugin manager
        $oHelperPluginManager = $this->getView()->getHelperPluginManager();

        // Retrieve form row helper
        $oFormRowHelper = $oHelperPluginManager->get('formRow');

        // Retrieve form collection helper
        $oFormCollectionHelper = $oHelperPluginManager->get('formCollection');

        // Retrieve button group helper
        $oButtonGroupHelper = $oHelperPluginManager->get('buttonGroup');

        // Store column option
        $bHasColumn = false;

        // Prepare options
        $sFormLayout = $oForm->getOption('layout');
        foreach ($oForm as $iKey => $oElement) {
            $aOptions = $oElement->getOptions();

            if (!$bHasColumn && !empty($aOptions['column'])) {
                $bHasColumn = true;
            }

            // Define layout option to form elements if not already defined
            if ($sFormLayout && empty($aOptions['layout'])) {
                $oElement->setOption('layout', $sFormLayout);
            }

            // Manage button group option
            if ($oElement instanceof \Zend\Form\FieldsetInterface) {
                $this->setClassesToElement($oElement, ['form-group']);
                $aElementsRendering[$iKey] = $oFormCollectionHelper->__invoke($oElement);
            } else {
                $aElementsRendering[$iKey] = $oFormRowHelper->__invoke($oElement);
            }
        }

        // Assemble elements rendering
        $sFormContent = '';

        foreach ($aElementsRendering as $sElementRendering) {
            if ($sElementRendering) {
                $sFormContent .= ($sFormContent ? PHP_EOL : '') . $sElementRendering;
            }
        }

        if ($bHasColumn && self::LAYOUT_HORIZONTAL !== $sFormLayout) {
            $sFormContent = $this->htmlElement(
                'div',
                ['class' => $oForm->getOption('row_class') ?? 'row'],
                $sFormContent
            );
        }

        return $sFormContent;
    }
}
