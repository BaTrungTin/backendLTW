<?php

namespace App\Helpers;

use App\Models\Category;

class CategoryHelper
{
    private static function normalizeParentId($parentId): string
    {
        if ($parentId === null || $parentId === '' || $parentId === 0 || $parentId === '0') {
            return '';
        }

        return (string) $parentId;
    }

    public static function buildCategoryTree(array $categories, $parentId = ''): array
    {
        $tree = [];
        $normalizedParentId = self::normalizeParentId($parentId);

        foreach ($categories as $item) {
            $parent = self::normalizeParentId($item['parent'] ?? '');
            if ($parent === $normalizedParentId) {
                $children = self::buildCategoryTree($categories, $item['id']);
                $tree[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'children' => $children,
                ];
            }
        }
        return $tree;
    }

    public static function getCategoryChild($parentId): array
    {
        $result = [];
        $childList = Category::find([
            'parent' => $parentId,
            'deleted' => false,
            'status' => 'active',
        ]);
        foreach ($childList as $item) {
            $result[] = ['id' => $item['id'], 'name' => $item['name']];
            $result = array_merge($result, self::getCategoryChild($item['id']));
        }
        return $result;
    }

    public static function getCategoryParent($parentId): array
    {
        $result = [];
        $categoryParent = Category::findOne(['id' => $parentId, 'deleted' => false]);
        if ($categoryParent) {
            $result[] = [
                'id' => $categoryParent['id'],
                'name' => $categoryParent['name'],
                'avatar' => $categoryParent['avatar'],
                'slug' => $categoryParent['slug'],
            ];
            if (!empty($categoryParent['parent'])) {
                $parentResult = self::getCategoryParent($categoryParent['parent']);
                if (!empty($parentResult)) {
                    array_unshift($result, $parentResult[0]);
                }
            }
        }
        return $result;
    }
}
