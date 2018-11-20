module.exports = {
    title: 'Scorecard',
    description: 'Grade elements',
    base: '/',
    themeConfig: {
        docsRepo: 'flipboxfactory/scorecard',
        docsDir: 'docs',
        docsBranch: 'master',
        editLinks: true,
        search: true,
        searchMaxSuggestions: 10,
        codeLanguages: {
            twig: 'Twig',
            php: 'PHP',
            json: 'JSON',
            // any other languages you want to include in code toggles...
        },
        nav: [
            {text: 'Details', link: 'https://flipboxdigital.com/software/scorecard'},
            {text: 'Documentation', link: 'https://scorecard.flipboxfactory.com'},
            {text: 'Changelog', link: 'https://github.com/flipboxfactory/scorecard/blob/master/CHANGELOG.md'},
            {text: 'Repo', link: 'https://github.com/flipboxfactory/scorecard'}
        ],
        sidebar: {
            '/': [
                {
                    title: 'Getting Started',
                    collapsable: false,
                    children: [
                        ['/', 'Introduction'],
                        ['installation', 'Installation / Upgrading'],
                        'support'
                    ]
                }
            ]
        }
    },
    markdown: {
        anchor: {
            level: [2, 3, 4]
        },
        toc: {
            includeLevel: [3]
        }
    }
}