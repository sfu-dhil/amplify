---
layout: default
title: Metadata
has_children: true
nav_order: 3
has_toc: false
---

# Metadata

{% assign child_pages = site[page.collection]
    | default: site.html_pages
    | where: "parent", page.title
    | where: "grand_parent", page.parent %}

{% include sorted_pages.html pages = child_pages %}

<ul>
{% for child in sorted_pages %}
  <li>
    <a href="{{ child.url | relative_url }}">{{ child.title }}</a>{% if child.summary %} - {{ child.summary }}{% endif %}
  </li>
{% endfor %}
</ul>