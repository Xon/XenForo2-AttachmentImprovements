<?php

namespace SV\AttachmentImprovements\XF\Attachment;

class Post extends XFCP_Post
{
    public function getConstraints(array $context)
    {
        $constraints = parent::getConstraints($context);

        $thread = null;
        $nodeId = null;
        $em = \XF::app()->em();

        if (!empty($extraContext['node_id']))
        {
            $nodeId = $extraContext['node_id'];
        }
        else if (!empty($extraContext['thread_id']))
        {
            /** @var \XF\Entity\Thread $thread */
            $thread = $em->find('XF:Thread', $extraContext['thread_id']);
            if ($thread)
            {
                $nodeId = $thread->node_id;
            }
        }
        else if (!empty($extraContext['post_id']))
        {
            /** @var \XF\Entity\Post $post */
            $post = $em->find('XF:Post', $extraContext['post_id']);
            if ($post)
            {
                /** @var \XF\Entity\Thread $thread */
                $thread = $em->find('XF:Thread', $post->thread_id);
                if ($thread)
                {
                    $nodeId = $thread->node_id;
                }
            }
        }

        if ($nodeId)
        {
            $constraints = $this->svUpdateConstraints($constraints, $nodeId, $thread);
        }

        return $constraints;
    }

    /**
     * @param array                  $constraints
     * @param int                    $nodeId
     * @param \XF\Entity\Thread|null $thread
     * @return array
     */
    protected function svUpdateConstraints(/** @noinspection PhpUnusedParameterInspection */ array $constraints, $nodeId, \XF\Entity\Thread $thread = null)
    {
        $visitor = \XF::visitor();

        $size = $visitor->hasNodePermission($nodeId, 'attach_size');
        if ($size > 0 && $size < $constraints['size'])
        {
            $constraints['size'] = $size * 1024;
        }
        $count = $visitor->hasNodePermission($nodeId, 'attach_count');
        if ($count > 0 && $count < $constraints['count'])
        {
            $constraints['count'] = $count;
        }

        return $constraints;
    }
}