---
layout: default
title: Schema
has_children: true
nav_order: 10
parent: Development Documentation
has_toc: false
---

<details markdown="block">
  <summary>
    Table of contents
  </summary>
  {: .text-delta }
1. TOC
{:toc}
</details>

# AMP

## Tables

| Name | Columns | Comment | Type |
| ---- | ------- | ------- | ---- |
| [contribution](contribution.md) | 8 |  | BASE TABLE |
| [episode](episode.md) | 19 |  | BASE TABLE |
| [person](person.md) | 9 |  | BASE TABLE |
| [podcast](podcast.md) | 17 |  | BASE TABLE |
| [publisher](publisher.md) | 9 |  | BASE TABLE |
| [season](season.md) | 10 |  | BASE TABLE |

## Relations

![er](schema.svg)

---

> Generated by [tbls](https://github.com/k1LoW/tbls)

<script>
    const linkList = [].slice.call(document.querySelectorAll('a[href$=".md"]'));
    linkList.map(function (linkEl) {
        linkEl.href = linkEl.href.replace('.md', '.html');
    });
</script>