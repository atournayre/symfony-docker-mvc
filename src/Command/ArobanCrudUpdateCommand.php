<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'aroban:crud:update',
    description: 'Mettre à jour un CRUD généré par le maker. Les templates sont traduits en français et rendu compatibles avec bootstrap.',
)]
class ArobanCrudUpdateCommand extends Command
{
    private string $cheminDesTemplates;
    private string $entite;

    /**
     * @param KernelInterface $kernel
     * @param string|null     $name
     */
    public function __construct(
        private KernelInterface $kernel,
        string                  $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('entite', InputArgument::REQUIRED, 'L\'entité associée au CRUD');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->entite = $input->getArgument('entite');
        $this->cheminDesTemplates = realpath(
            implode(DIRECTORY_SEPARATOR, [
                $this->kernel->getProjectDir(),
                'templates',
                strtolower($this->entite),
            ])
        );

        if (!file_exists($this->cheminDesTemplates)) {
            throw new DirectoryNotFoundException();
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->mettreAJourLeFormulaireDeSuppression();
        $this->mettreAJourLeFormulaire();
        $this->mettreAJourLeTemplateDeModification();
        $this->mettreAJourLeTemplateDeListe();
        $this->mettreAJourLeTemplateDeCreation();
        $this->mettreAJourLeTemplateDeConsultation();

        $io->success('Terminé.');

        return Command::SUCCESS;
    }

    private function mettreAJourLeFormulaireDeSuppression(): void
    {
        $cheminDuFichier = $this->recupererLeCheminDuFichier('_delete_form.html.twig');
        if (!file_exists($cheminDuFichier)) {
            return;
        }

        $patternsARemplacer = [
            'Are you sure you want to delete this item?' => 'Êtes vous sûr de vouloir supprimer cet élément ?',
            '<button class="btn">Delete</button>' => '<button class="btn btn-danger">Supprimer</button>',
        ];
        $this->remplacerLesPatternsDUnFichier($patternsARemplacer, $cheminDuFichier);
    }

    /**
     * @param string $cheminDuFichier
     *
     * @return string
     */
    private function recupererLeCheminDuFichier(string $cheminDuFichier): string
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->cheminDesTemplates,
            $cheminDuFichier,
        ]);
    }

    /**
     * @param array  $patternsARemplacer
     * @param string $cheminDuFichier
     *
     * @return void
     */
    private function remplacerLesPatternsDUnFichier(array $patternsARemplacer, string $cheminDuFichier): void
    {
        if (empty($patternsARemplacer)) {
            return;
        }

        $contenuDuFichier = str_replace(
            array_keys($patternsARemplacer),
            array_values($patternsARemplacer),
            file_get_contents($cheminDuFichier)
        );

        file_put_contents($cheminDuFichier, $contenuDuFichier);
    }

    /**
     * @return void
     */
    private function mettreAJourLeFormulaire(): void
    {
        $cheminDuFichier = $this->recupererLeCheminDuFichier('_form.html.twig');
        if (!file_exists($cheminDuFichier)) {
            return;
        }

        $patternsARemplacer = [
            '<button class="btn">{{ button_label|default(\'Save\') }}</button>' => '<button class="btn btn-success">{{ button_label|default(\'Enregistrer\') }}</button>',
        ];
        $this->remplacerLesPatternsDUnFichier($patternsARemplacer, $cheminDuFichier);
    }

    /**
     * @return void
     */
    private function mettreAJourLeTemplateDeModification(): void
    {
        $cheminDuFichier = $this->recupererLeCheminDuFichier('edit.html.twig');
        if (!file_exists($cheminDuFichier)) {
            return;
        }

        $patternsARemplacer = [
            sprintf('{%% block title %%}Edit %s{%% endblock %%}',
                $this->entite) => sprintf('{%% block title %%}Modifier %s{%% endblock %%}', $this->entite),
            sprintf('<h1>Edit %s</h1>', $this->entite) => sprintf('<h1>Modifier %s</h1>', $this->entite),
            '{\'button_label\': \'Update\'}' => '{\'button_label\': \'Modifier\'}',
            '>back to list' => ' class="btn btn-secondary">Retour à la liste',
        ];
        $this->remplacerLesPatternsDUnFichier($patternsARemplacer, $cheminDuFichier);
    }

    /**
     * @return void
     */
    private function mettreAJourLeTemplateDeListe(): void
    {
        $cheminDuFichier = $this->recupererLeCheminDuFichier('index.html.twig');
        if (!file_exists($cheminDuFichier)) {
            return;
        }

        $patternsARemplacer = [
            sprintf('{%% block title %%}%s index{%% endblock %%}',
                $this->entite) => sprintf('{%% block title %%}Liste des %ss{%% endblock %%}', $this->entite),
            sprintf('<h1>%s index</h1>', $this->entite) => sprintf('<h1>Liste des %ss</h1>', $this->entite),
            '>show' => ' class="btn btn-secondary">Voir',
            '>edit' => ' class="btn btn-primary">Modifier',
            'no records found' => 'Aucun résultat',
            '>Create new' => ' class="btn btn-success">Créer',
            '<th>actions</th>' => '<th>Actions</th>',
        ];
        $this->remplacerLesPatternsDUnFichier($patternsARemplacer, $cheminDuFichier);
    }

    /**
     * @return void
     */
    private function mettreAJourLeTemplateDeCreation(): void
    {
        $cheminDuFichier = $this->recupererLeCheminDuFichier('new.html.twig');
        if (!file_exists($cheminDuFichier)) {
            return;
        }

        $patternsARemplacer = [
            sprintf('{%% block title %%}New %s{%% endblock %%}',
                $this->entite) => sprintf('{%% block title %%}%s %s{%% endblock %%}',
                $this->determinerNouveauOuNouvel(), $this->entite),
            sprintf('<h1>Create new %s</h1>', $this->entite) => sprintf('<h1>Créer un %s %s</h1>',
                strtolower($this->determinerNouveauOuNouvel()), $this->entite),
            '>back to list' => ' class="btn btn-secondary">Retour à la liste',
        ];
        $this->remplacerLesPatternsDUnFichier($patternsARemplacer, $cheminDuFichier);
    }

    /**
     * @return string
     */
    private function determinerNouveauOuNouvel(): string
    {
        $premiereLettre = strtolower(substr($this->entite, 0, 1));
        if (in_array($premiereLettre, [
            'a',
            'e',
            'i',
            'o',
            'u',
            'y',
        ])) {
            return 'Nouvel';
        }
        return 'Nouveau';
    }

    /**
     * @return void
     */
    private function mettreAJourLeTemplateDeConsultation(): void
    {
        $cheminDuFichier = $this->recupererLeCheminDuFichier('show.html.twig');
        if (!file_exists($cheminDuFichier)) {
            return;
        }

        $patternsARemplacer = [
            '>back to list' => ' class="btn btn-secondary">Retour à la liste',
            '>edit' => ' class="btn btn-primary">Modifier',
        ];
        $this->remplacerLesPatternsDUnFichier($patternsARemplacer, $cheminDuFichier);
    }
}
