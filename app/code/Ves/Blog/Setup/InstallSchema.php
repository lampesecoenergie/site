<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Blog
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Blog\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Drop table if exists
         */
        $setup->getConnection()->dropTable($setup->getTable('ves_blog_category'));
        $setup->getConnection()->dropTable($setup->getTable('ves_blog_category_store'));
        $setup->getConnection()->dropTable($setup->getTable('ves_blog_post'));
        $setup->getConnection()->dropTable($setup->getTable('ves_blog_post_category'));
        $setup->getConnection()->dropTable($setup->getTable('ves_blog_post_tag'));
        $setup->getConnection()->dropTable($setup->getTable('ves_blog_post_related'));
        $setup->getConnection()->dropTable($setup->getTable('ves_blog_post_store'));
        $setup->getConnection()->dropTable($setup->getTable('ves_blog_comment'));
        $setup->getConnection()->dropTable($setup->getTable('ves_blog_comment_store'));

        /**
         * Create table 'ves_blog_category'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_category')
        )
        ->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Category ID'
        )
        ->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )
        ->addColumn(
            'identifier',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Identifier'
        )
        ->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Description'
        )
        ->addColumn(
            'image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Image'
        )
        ->addColumn(
            'layout_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Layout'
        )
        ->addColumn(
            'orderby',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Order By'
        )
        ->addColumn(
            'comments',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Enable Comments'
        )
        ->addColumn(
            'item_per_page',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Number Post Per Page'
        )
        ->addColumn(
            'lg_column_item',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Number Column on Large Desktop'
        )
        ->addColumn(
            'md_column_item',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Number Column on Desktop'
        )
        ->addColumn(
            'sm_column_item',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Number Column on Tablets'
        )
        ->addColumn(
            'xs_column_item',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Number Column on Phones'
        )
        ->addColumn(
            'page_layout',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Page Layout'
        )
        ->addColumn(
            'page_title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Meta Title'
        )
        ->addColumn(
            'canonical_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Canonical Url'
        )
        ->addColumn(
            'layout_update_xml',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Layout Update XML'
        )
        ->addColumn(
            'meta_keywords',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Meta Keywords'
        )
        ->addColumn(
            'meta_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Meta Description'
        )
        ->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Category Creation Time'
        )
        ->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Category Modification Time'
        )
        ->addColumn(
            'cat_position',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Position'
        )
        ->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Is Active'
        )
        ->addIndex(
            $setup->getIdxName(
                $installer->getTable('ves_blog_category'),
                ['name', 'identifier', 'description'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['name', 'identifier', 'description'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )
        ->setComment(
            'Blog - Category Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ves_blog_category_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_category_store')
        )->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Category ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('ves_blog_category_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('ves_blog_category_store', 'category_id', 'ves_blog_category', 'category_id'),
            'category_id',
            $installer->getTable('ves_blog_category'),
            'category_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ves_blog_category_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'CMS Page To Store Linkage Table'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'ves_blog_post'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_post')
        )
        ->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Post ID'
        )
        ->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Title'
        )
        ->addColumn(
            'identifier',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Identifier'
        )
        ->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Content'
        )
        ->addColumn(
            'short_content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Short Content'
        )
        ->addColumn(
            'user_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'User ID'
        )
        ->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Is Active'
        )
        ->addColumn(
            'image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Image'
        )
        ->addColumn(
            'image_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Image Type'
        )
        ->addColumn(
            'image_video_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Image Video Type'
        )
        ->addColumn(
            'image_video_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Image Video Id'
        )
        ->addColumn(
            'thumbnail',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Thumbnail'
        )
        ->addColumn(
            'thumbnail_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Thumbnail Type'
        )
        ->addColumn(
            'thumbnail_video_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Thumbnail Video Type'
        )
        ->addColumn(
            'thumbnail_video_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Thumbnail Video Id'
        )
         ->addColumn(
            'page_layout',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Page Layout'
        )
        ->addColumn(
            'page_title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Meta Title'
        )
        ->addColumn(
            'meta_keywords',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Meta Keywords'
        )
        ->addColumn(
            'meta_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Meta Description'
        )
        ->addColumn(
            'canonical_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Canonical Url'
        )
        ->addColumn(
            'tags',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Tags'
        )
        ->addColumn(
            'hits',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Hits'
        )
        ->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Post Creation Time'
        )
        ->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Post Modification Time'
        )
        ->addColumn(
            'enable_comment',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Enable Comment'
        )
        ->addIndex(
            $setup->getIdxName(
                $installer->getTable('ves_blog_post'),
                ['name', 'identifier', 'description'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['title', 'identifier', 'content', 'short_content'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )
        ->setComment(
            'Blog - Post Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ves_blog_post_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_post_category')
        )->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Post ID'
        )->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Category ID'
        )
        ->addColumn(
            'position',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Position'
        )->addIndex(
            $installer->getIdxName('ves_blog_post_category', ['category_id']),
            ['category_id']
        )->addForeignKey(
            $installer->getFkName('ves_blog_post_category_post', 'post_id', 'ves_blog_post', 'post_id'),
            'post_id',
            $installer->getTable('ves_blog_post'),
            'post_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ves_blog_post_category_category', 'category_id', 'ves_blog_category', 'category_id'),
            'category_id',
            $installer->getTable('ves_blog_category'),
            'category_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Post To Category Linkage Table'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'ves_blog_post_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_post_tag')
        )->addColumn(
            'tag_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Tag ID'
        )->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Post ID'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'alias',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addForeignKey(
            $installer->getFkName('ves_blog_post_tag', 'post_id', 'ves_blog_post', 'post_id'),
            'post_id',
            $installer->getTable('ves_blog_post'),
            'post_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Post To Category Linkage Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ves_blog_post_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_post_related')
        )->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Post ID'
        )->addColumn(
            'post_related_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Post Related ID'
        )
        ->addColumn(
            'position',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Position'
        )
        ->addForeignKey(
            $installer->getFkName('ves_blog_post_related_post', 'post_id', 'ves_blog_post', 'post_id'),
            'post_id',
            $installer->getTable('ves_blog_post'),
            'post_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ves_blog_post_related_post_id', 'post_related_id', 'ves_blog_post', 'category_id'),
            'post_id',
            $installer->getTable('ves_blog_post'),
            'post_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Post To Category Linkage Table'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'ves_blog_post_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_post_store')
        )->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Post ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('ves_blog_post_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('ves_blog_post_store', 'post_id', 'ves_blog_post', 'post_id'),
            'post_id',
            $installer->getTable('ves_blog_post'),
            'post_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ves_blog_post_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Post To Store Linkage Table'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'ves_blog_category'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_comment')
        )
        ->addColumn(
            'comment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Comment ID'
        )
        ->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Post ID'
        )
        ->addColumn(
            'position',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Position'
        )
        ->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Content'
        )
        ->addColumn(
            'user_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'User Name'
        )
        ->addColumn(
            'user_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'User Email'
        )
        ->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Comment Creation Time'
        )
        ->addColumn(
            'has_read',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Has Read'
        )
        ->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Is Active'
        )
        ->addIndex(
            $setup->getIdxName(
                $installer->getTable('ves_blog_comment'),
                ['cotent'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['content'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )
        ->addForeignKey(
            $installer->getFkName('ves_blog_comment', 'post_id', 'ves_blog_post', 'post_id'),
            'post_id',
            $installer->getTable('ves_blog_post'),
            'post_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )
        ->setComment(
            'Blog - Comment Table'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'ves_blog_post_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_comment_store')
        )->addColumn(
            'comment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Comment ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('ves_blog_post_store', ['store_id']),
            ['store_id']
        )
        ->addForeignKey(
            $installer->getFkName('ves_blog_comment_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Post To Store Linkage Table'
        );
        $installer->getConnection()->createTable($table);
    }
}