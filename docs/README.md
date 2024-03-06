# Amplify Docs

Documentation for the Amplify Podcast Network preservation tool


> The SSHRC-funded Amplify Podcast Network encourages collaboration and experimentation via the medium of scholarly podcasting, with a focus on podcasts committed to anti-racism, feminist social justice, and community-building. We’re committed to supporting the creation of new scholarly podcasts, while also building the infrastructure that will support them, from new peer review processes to digital preservation tools to open access guides to making your own podcast. <br/><br/>Amplify represents scholarship that contributes to collective, public knowledge, born of research across the many disciplines and interdisciplines that constitute humanities and social sciences research. Our podcasts explicitly or implicitly engage with the question of what constitutes scholarship by pushing at boundaries, whether they are formal, methodological, theoretical, or otherwise.

— [Amplify Podcast Network website](https://amplifypodcastnetwork.ca/about/)

![Amplify Podcast Network Logo](https://i0.wp.com/amplifypodcastnetwork.ca/wp-content/uploads/2022/05/Amplify-Final-Logo-1-copy-1-edited.png?fit=1200%2C1200&ssl=1&w=640 "Amplify Logo")


## Run tbls

From the docs folder (with amplify running)

    docker run --rm -v $PWD:/work -w /work --network host ghcr.io/k1low/tbls doc --rm-dist

## Update deps

From the docs folder

    docker run --rm -v $PWD:/work -w /work ruby:3.3 bundle update