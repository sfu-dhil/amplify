---
layout: default
title: How do I...
has_children: true
nav_order: 2
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

# How do I...

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

# Navigation Pages

## Homepage

![homepage](images/homepage.png)
{: .text-center }

1. __RSS Import:__ Navigate to import page where you can input RSS URL for import
2. __New:__ Create a new podcast from scratch (enter fields manually)
3. __Title:__ The podcast title, and link to the podcast page on AMPLIFY
4. __Website:__ The URL for the podcast website and link to it
5. __Exportable Status:__ Indicates missing fields which must be resolved before export
6. __Podcasts (menu):__ Side menu listing a user’s imported podcasts
7. __Contributors:__ Side menu listing saved entries for fields related to people and institutions
8. __Documentation:__ Side menu listing and linking to documentation of how to use tool
9. __Privacy:__ Information on our policies regarding the collection, use, and disclosure of personal data
10. __User Account:__ Drop-down menu to navigate to Profile, Change Password, and Logout



## Podcast Page

![podcast](images/podcast.png)
{: .text-center }

1. __Share:__ Share podcast with other Amplify users. Allows collaborative editing of metadata
2. __Export:__ Export your podcast metadata in a variety of formats
3. __RSS Import:__ Import additional/new material from your RSS feed
4. __Edit:__ Edit the podcast metadata
5. __Delete (podcast):__ Delete the podcast from the Amplify server
6. __Breadcrumb Trail Menu:__ Quick navigation to parent pages
7. __View Season:__ View a season's metadata
8. __Edit Season:__ Edit metadata for a season
9. __Delete (season):__ Delete a season from the Amplify server
10. __Episode #:__ The season and episode number and link to the episode page
11. __Episode Title:__ The title of the episode and link to the episode page
12. __Episode Exportable Status:__ Indicates missing metadata required for exporting an episode
13. __Last Updated:__ Indicates the date the episode was last updated
14. __Edit (episode):__ Edit metadata for an episode
15. __Delete (episode):__ Delete an episode from the Amplify server