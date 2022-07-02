<?php

declare(strict_types=1);

namespace Derhansen\FormCrshield\Hooks;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class Form
{
    const FIELD_ID = 'cr-field';

    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    public function afterInitializeCurrentPage(FormRuntime $runtime, ?Page $currentPage, ?Page $page, array $args)
    {
        $pageObject = $currentPage ?? $page;

        if ($pageObject) {
            $pageMaxLifetime = $this->getPageMaxLifetime($runtime->getRequest());
            $expirationTime = time() + $pageMaxLifetime;
            $challenge = $expirationTime . '|' . GeneralUtility::hmac($expirationTime, $runtime->getIdentifier());

            $newElement = $pageObject->createElement(self::FIELD_ID, 'Hidden');
            $newElement->addValidator(new NotEmptyValidator());
            $newElement->setProperty('fluidAdditionalAttributes', ['data-cr-challenge' => base64_encode($challenge)]);
        }

        return $currentPage;
    }

    public function afterSubmit(FormRuntime $runtime, $element, $value, $requestArguments)
    {
        if (!is_a($element, GenericFormElement::class) || $element->getIdentifier() !== self::FIELD_ID) {
            return $value;
        }

        $submittedResponse = base64_decode($requestArguments[self::FIELD_ID] ?? '');
        if (!str_contains($submittedResponse, '|')) {
            $this->logger->debug('CR response invalid. Submitted data', $requestArguments);
            return '';
        }

        [$expirationTime, $clientData] = explode('|', $submittedResponse);
        $knownHmac = GeneralUtility::hmac($expirationTime, $runtime->getIdentifier());
        $calculatedData = str_rot13($knownHmac);

        if ($calculatedData !== $clientData) {
            $this->logger->debug('CR response missmatch. Submitted data', $requestArguments);
            return '';
        }

        if ((int)$expirationTime <= time()) {
            $this->logger->debug('CR response expired. Submitted data', $requestArguments);
            return '';
        }

        return $value;
    }

    protected function getPageMaxLifetime(ServerRequestInterface $request): int
    {
        $tsfe = $request->getAttribute('frontend.controller');
        return $tsfe ? $tsfe->get_cache_timeout() : 86400;
    }
}