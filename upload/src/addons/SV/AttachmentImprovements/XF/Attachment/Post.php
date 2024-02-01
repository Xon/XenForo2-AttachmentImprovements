<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\AttachmentImprovements\XF\Attachment;

use XF\Entity\Thread as ThreadEntity;

class Post extends XFCP_Post
{
    public function getConstraints(array $context)
    {
        $constraints = parent::getConstraints($context);

        $thread = null;
        $nodeId = 0;
        $em = \XF::app()->em();

        if (!empty($context['node_id']))
        {
            $nodeId = (int)$context['node_id'];
        }
        else if (!empty($context['thread_id']))
        {
            /** @var ThreadEntity $thread */
            $thread = $em->find('XF:Thread', $context['thread_id']);
            if ($thread !== null)
            {
                $nodeId = $thread->node_id;
            }
        }
        else if (!empty($context['post_id']))
        {
            /** @var \XF\Entity\Post $post */
            $post = $em->find('XF:Post', $context['post_id']);
            if ($post)
            {
                /** @var ThreadEntity $thread */
                $thread = $em->find('XF:Thread', $post->thread_id);
                if ($thread !== null)
                {
                    $nodeId = $thread->node_id;
                }
            }
        }

        if ($nodeId !== 0)
        {
            $constraints = $this->svUpdateConstraints($constraints, $nodeId, $thread);
        }

        return $constraints;
    }

    /**
     * @param array             $constraints
     * @param int               $nodeId
     * @param ThreadEntity|null $thread
     * @return array
     * @noinspection PhpUnusedParameterInspection
     */
    protected function svUpdateConstraints(array $constraints, int $nodeId, ThreadEntity $thread = null)
    {
        $visitor = \XF::visitor();

        $size = (int)$visitor->hasNodePermission($nodeId, 'attach_size');
        if ($size > 0 && $size < $constraints['size'])
        {
            $constraints['size'] = $size * 1024;
        }
        $count = (int)$visitor->hasNodePermission($nodeId, 'attach_count');
        if ($count > 0 && $count < $constraints['count'])
        {
            $constraints['count'] = $count;
        }

        return $constraints;
    }
}