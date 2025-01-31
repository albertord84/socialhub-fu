<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * TagLayoutContent.
 *
 * @method ExploreItemInfo getExploreItemInfo()
 * @method string getFeedType()
 * @method TagMedia[] getMedias()
 * @method Tag[] getRelated()
 * @method string getRelatedStyle()
 * @method TabsInfo getTabsInfo()
 * @method bool isExploreItemInfo()
 * @method bool isFeedType()
 * @method bool isMedias()
 * @method bool isRelated()
 * @method bool isRelatedStyle()
 * @method bool isTabsInfo()
 * @method $this setExploreItemInfo(ExploreItemInfo $value)
 * @method $this setFeedType(string $value)
 * @method $this setMedias(TagMedia[] $value)
 * @method $this setRelated(Tag[] $value)
 * @method $this setRelatedStyle(string $value)
 * @method $this setTabsInfo(TabsInfo $value)
 * @method $this unsetExploreItemInfo()
 * @method $this unsetFeedType()
 * @method $this unsetMedias()
 * @method $this unsetRelated()
 * @method $this unsetRelatedStyle()
 * @method $this unsetTabsInfo()
 */
class TagLayoutContent extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'related_style'     => 'string',
        'related'           => 'Tag[]',
        'medias'            => 'TagMedia[]',
        'feed_type'         => 'string',
        'explore_item_info' => 'ExploreItemInfo',
        'tabs_info'         => 'TabsInfo',
    ];
}