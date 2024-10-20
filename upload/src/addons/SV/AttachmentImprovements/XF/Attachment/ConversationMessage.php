<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\AttachmentImprovements\XF\Attachment;

/**
 * @extends \XF\Attachment\ConversationMessage
 */
class ConversationMessage extends XFCP_ConversationMessage
{
    /** @noinspection PhpUnnecessaryLocalVariableInspection */
    public function getConstraints(array $context)
    {
        $constraints = parent::getConstraints($context);

        $constraints = $this->svUpdateConstraints($constraints);

        return $constraints;
    }

    protected function svUpdateConstraints(array $constraints): array
    {
        $visitor = \XF::visitor();

        $size = (int)$visitor->hasPermission('conversation', 'attach_size');
        if ($size > 0 && $size < $constraints['size'])
        {
            $constraints['size'] = $size * 1024;
        }
        $count = (int)$visitor->hasPermission('conversation', 'attach_count');
        if ($count > 0 && $count < $constraints['count'])
        {
            $constraints['count'] = $count;
        }

        return $constraints;
    }
}