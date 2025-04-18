---
layout: default
title: publisher
parent: Schema
grand_parent: Development Documentation
---

<details markdown="block">
  <summary>
    Table of contents
  </summary>
  {: .text-delta }
1. TOC
{:toc}
</details>

# `publisher`

## Description

## Columns

|Name|Type|Default|Nullable|Extra Definition|Children|Parents|Comment|
|----|----|-------|--------|----------------|--------|-------|-------|
|id|int(11)||false|auto_increment|[podcast](podcast.md) [season](season.md)|||
|name|varchar(255)||false|||||
|location|varchar(255)|NULL|true|||||
|website|varchar(255)|NULL|true|||||
|description|longtext||false|||||
|contact|longtext||false|||||
|created|datetime||false||||(DC2Type:datetime_immutable)|
|updated|datetime||false||||(DC2Type:datetime_immutable)|
|podcast_id|int(11)||false|||[podcast](podcast.md)||

## Constraints

| Name | Type | Definition |
| ---- | ---- | ---------- |
| FK_9CE8D546786136AB | FOREIGN KEY | FOREIGN KEY (podcast_id) REFERENCES podcast (id) |
| PRIMARY | PRIMARY KEY | PRIMARY KEY (id) |

## Indexes

| Name | Definition |
| ---- | ---------- |
| IDX_9CE8D546786136AB | KEY IDX_9CE8D546786136AB (podcast_id) USING BTREE |
| publisher_ft | KEY publisher_ft (name, description) USING FULLTEXT |
| PRIMARY | PRIMARY KEY (id) USING BTREE |

## Relations

![er](publisher.svg)

---

> Generated by [tbls](https://github.com/k1LoW/tbls)

<script>
    const linkList = [].slice.call(document.querySelectorAll('a[href$=".md"]'));
    linkList.map(function (linkEl) {
        linkEl.href = linkEl.href.replace('.md', '.html');
    });
</script>