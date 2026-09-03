import { usePage } from '@inertiajs/vue3';

const es: Record<string, string> = {
    'About the book': 'Sobre el libro',
    Appearance: 'Apariencia',
    'Appearance settings': 'Configuración de apariencia',
    'Are you sure you want to delete your account?':
        '¿Seguro que quieres eliminar tu cuenta?',
    Cancel: 'Cancelar',
    'Confirm password': 'Confirmar contraseña',
    Dark: 'Oscuro',
    'Delete account': 'Eliminar cuenta',
    'Delete your account and all of its resources':
        'Elimina tu cuenta y todos sus recursos',
    'Email address': 'Correo electrónico',
    'Ensure your account is using a long, random password to stay secure':
        'Usa una contraseña larga y aleatoria para mantener tu cuenta segura',
    English: 'Inglés',
    French: 'Francés',
    Dashboard: 'Panel',
    Edit: 'Editar',
    Feed: 'Feed',
    'Find a book you would love to help shape.':
        'Encuentra un libro al que te encantaría ayudar a dar forma.',
    'Listing unavailable': 'Anuncio no disponible',
    'Manage how you take part in BetaReadr.':
        'Gestiona cómo participas en BetaReadr.',
    Interested: 'Me interesa',
    Pass: 'Pasar',
    'Open Listing': 'Anuncio abierto',
    words: 'palabras',
    'Reading Brief': 'Resumen de lectura',
    'Drag left to pass or right if you’re interested.':
        'Arrastra a la izquierda para pasar o a la derecha si te interesa.',
    'You’re all caught up': 'Estás al día',
    'Check back later for new Listings.':
        'Vuelve más tarde para ver nuevos anuncios.',
    View: 'Ver',
    'Your author profile': 'Tu perfil de autor',
    'Your beta reader profile': 'Tu perfil de lector beta',
    'This is the public identity readers see on BetaReadr.':
        'Esta es la identidad pública que ven los lectores en BetaReadr.',
    'This is the public identity authors see on BetaReadr.':
        'Esta es la identidad pública que ven los autores en BetaReadr.',
    'Create your author profile': 'Crea tu perfil de autor',
    'Create author profile': 'Crear perfil de autor',
    'Create your beta reader profile': 'Crea tu perfil de lector beta',
    'Create beta reader profile': 'Crear perfil de lector beta',
    'Introduce yourself to beta readers with a public name, unique handle, and optional bio.':
        'Preséntate a los lectores beta con un nombre público, un identificador único y una biografía opcional.',
    'Introduce yourself to authors with a public name, unique handle, and optional bio.':
        'Preséntate a los autores con un nombre público, un identificador único y una biografía opcional.',
    Language: 'Idioma',
    Light: 'Claro',
    Name: 'Nombre',
    'New password': 'Nueva contraseña',
    'Current password': 'Contraseña actual',
    Password: 'Contraseña',
    'Please proceed with caution, this cannot be undone.':
        'Continúa con cuidado; esta acción no se puede deshacer.',
    Profile: 'Perfil',
    'Profile settings': 'Configuración del perfil',
    Save: 'Guardar',
    Security: 'Seguridad',
    'Security settings': 'Configuración de seguridad',
    Settings: 'Configuración',
    Spanish: 'Español',
    System: 'Sistema',
    'Manage your profile and account settings':
        'Gestiona la configuración de tu perfil y cuenta',
    'Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.':
        'Al eliminar tu cuenta, todos sus recursos y datos se borrarán permanentemente. Introduce tu contraseña para confirmarlo.',
    'Update password': 'Actualizar contraseña',
    'Update the appearance settings for your account':
        'Actualiza la apariencia de tu cuenta',
    'Update your name, email address, and language':
        'Actualiza tu nombre, correo electrónico e idioma',
    Warning: 'Advertencia',
    'Log out': 'Cerrar sesión',
    Platform: 'Plataforma',
    Books: 'Libros',
    'Add book': 'Añadir libro',
    'Add a book': 'Añadir un libro',
    'Add your first book': 'Añade tu primer libro',
    'Add a work to your public author profile.':
        'Añade una obra a tu perfil público de autor.',
    'Book title': 'Título del libro',
    'Changes appear immediately on your public author profile.':
        'Los cambios aparecen inmediatamente en tu perfil público de autor.',
    Delete: 'Eliminar',
    'Delete book': 'Eliminar libro',
    Description: 'Descripción',
    'Edit book': 'Editar libro',
    Listings: 'Anuncios',
    'Manage the books shown on your public author profile.':
        'Gestiona los libros que aparecen en tu perfil público de autor.',
    'Books you add are immediately visible on your public author profile.':
        'Los libros que añadas aparecerán inmediatamente en tu perfil público de autor.',
    'No description': 'Sin descripción',
    '(optional)': '(opcional)',
    'Save changes': 'Guardar cambios',
    'Tell readers about this book.':
        'Háblales a los lectores sobre este libro.',
    'This book will be permanently deleted. This cannot be undone.':
        'Este libro se eliminará permanentemente. Esta acción no se puede deshacer.',
    Title: 'Título',
    'Log in': 'Iniciar sesión',
    Register: 'Registrarse',
    'Thoughtful feedback before publication':
        'Comentarios reflexivos antes de publicar',
    'Better books begin with thoughtful beta readers':
        'Los mejores libros comienzan con lectores beta atentos',
    'Find the feedback your book needs':
        'Encuentra los comentarios que tu libro necesita',
    'before publication.': 'antes de publicarlo.',
    'BetaReadr brings authors seeking useful, pre-publication feedback together with beta readers who want to help shape books they care about.':
        'BetaReadr conecta a autores que buscan comentarios útiles antes de publicar con lectores beta que quieren ayudar a dar forma a libros que les importan.',
    'Register for BetaReadr': 'Regístrate en BetaReadr',
    'Go to your Dashboard': 'Ir a tu panel',
    'In progress': 'En curso',
    Interest: 'Interés',
    Match: 'Coincidencia',
    Feedback: 'Comentarios',
    'Two paths, one community': 'Dos caminos, una comunidad',
    'Take part in the story': 'Forma parte de la historia',
    'For authors': 'Para autores',
    'For beta readers': 'Para lectores beta',
    'Your next chapter starts here.': 'Tu próximo capítulo empieza aquí.',
    'Thoughtful feedback for books before publication.':
        'Comentarios reflexivos para libros antes de su publicación.',
    'BetaReadr connects authors seeking thoughtful pre-publication feedback with beta readers who want to help shape a book.':
        'BetaReadr conecta a autores que buscan comentarios atentos antes de publicar con lectores beta que quieren ayudar a dar forma a un libro.',
    'Create the profile that fits how you want to use BetaReadr. You can be both an author and a beta reader.':
        'Crea el perfil que se ajuste a cómo quieres usar BetaReadr. Puedes ser autor y lector beta.',
    'Present your work clearly and explain the thoughtful pre-publication feedback you need.':
        'Presenta tu obra con claridad y explica los comentarios que necesitas antes de publicarla.',
    'Create your public author profile.': 'Crea tu perfil público de autor.',
    'Add a book you want feedback on.':
        'Añade un libro sobre el que quieras recibir comentarios.',
    'Open a Beta Reading Listing with a focused Reading Brief.':
        'Publica un anuncio de lectura beta con un resumen bien enfocado.',
    'Share what you enjoy reading and prepare to find pre-publication books where your perspective can help.':
        'Comparte qué te gusta leer y encuentra libros aún no publicados en los que tu perspectiva pueda ayudar.',
    'Create your public beta reader profile.':
        'Crea tu perfil público de lector beta.',
    'Use your bio to share your reading interests and experience.':
        'Usa tu biografía para compartir tus intereses y experiencia lectora.',
    'Discovery and matching with authors and their books are still to come.':
        'La búsqueda y conexión con autores y sus libros llegará próximamente.',
    'Join BetaReadr as an author, a beta reader, or both—and help make pre-publication feedback more thoughtful.':
        'Únete a BetaReadr como autor, lector beta o ambos y ayuda a mejorar los comentarios antes de la publicación.',
};

const fr: Record<string, string> = {
    Appearance: 'Apparence',
    'Appearance settings': 'Paramètres d’apparence',
    'Are you sure you want to delete your account?':
        'Voulez-vous vraiment supprimer votre compte ?',
    Cancel: 'Annuler',
    'Confirm password': 'Confirmer le mot de passe',
    Dark: 'Sombre',
    Dashboard: 'Tableau de bord',
    'Delete account': 'Supprimer le compte',
    'Delete your account and all of its resources':
        'Supprimez votre compte et toutes ses ressources',
    Edit: 'Modifier',
    'Email address': 'Adresse e-mail',
    English: 'Anglais',
    Feed: 'Fil',
    Feedback: 'Commentaires',
    French: 'Français',
    Interested: 'Intéressé',
    Language: 'Langue',
    Light: 'Clair',
    'Log in': 'Se connecter',
    'Log out': 'Se déconnecter',
    Name: 'Nom',
    'New password': 'Nouveau mot de passe',
    'Current password': 'Mot de passe actuel',
    Password: 'Mot de passe',
    Pass: 'Passer',
    Platform: 'Plateforme',
    Profile: 'Profil',
    'Profile settings': 'Paramètres du profil',
    Register: 'S’inscrire',
    Save: 'Enregistrer',
    Security: 'Sécurité',
    'Security settings': 'Paramètres de sécurité',
    Settings: 'Paramètres',
    Spanish: 'Espagnol',
    System: 'Système',
    View: 'Voir',
    Warning: 'Avertissement',
    Books: 'Livres',
    'Add book': 'Ajouter un livre',
    'Add a book': 'Ajouter un livre',
    'Add your first book': 'Ajoutez votre premier livre',
    'Add a work to your public author profile.':
        'Ajoutez une œuvre à votre profil public d’auteur.',
    'Book title': 'Titre du livre',
    'Changes appear immediately on your public author profile.':
        'Les modifications apparaissent immédiatement sur votre profil public d’auteur.',
    Delete: 'Supprimer',
    'Delete book': 'Supprimer le livre',
    Description: 'Description',
    'Edit book': 'Modifier le livre',
    Listings: 'Annonces',
    'Manage the books shown on your public author profile.':
        'Gérez les livres affichés sur votre profil public d’auteur.',
    'Books you add are immediately visible on your public author profile.':
        'Les livres ajoutés sont immédiatement visibles sur votre profil public d’auteur.',
    'No description': 'Aucune description',
    '(optional)': '(facultatif)',
    'Save changes': 'Enregistrer les modifications',
    'Tell readers about this book.': 'Présentez ce livre aux lecteurs.',
    'This book will be permanently deleted. This cannot be undone.':
        'Ce livre sera définitivement supprimé. Cette action est irréversible.',
    Title: 'Titre',
    words: 'mots',
    'About the book': 'À propos du livre',
    'Reading Brief': 'Brief de lecture',
    'Open Listing': 'Annonce ouverte',
    'Listing unavailable': 'Annonce indisponible',
    'Find a book you would love to help shape.':
        'Trouvez un livre que vous aimeriez contribuer à façonner.',
    'Drag left to pass or right if you’re interested.':
        'Faites glisser à gauche pour passer ou à droite si vous êtes intéressé.',
    'You’re all caught up': 'Vous êtes à jour',
    'Check back later for new Listings.':
        'Revenez plus tard pour de nouvelles annonces.',
    'Manage how you take part in BetaReadr.':
        'Gérez votre participation à BetaReadr.',
    'Your author profile': 'Votre profil d’auteur',
    'Your beta reader profile': 'Votre profil de bêta-lecteur',
    'This is the public identity readers see on BetaReadr.':
        'Voici l’identité publique que les lecteurs voient sur BetaReadr.',
    'This is the public identity authors see on BetaReadr.':
        'Voici l’identité publique que les auteurs voient sur BetaReadr.',
    'Create your author profile': 'Créez votre profil d’auteur',
    'Create author profile': 'Créer un profil d’auteur',
    'Create your beta reader profile': 'Créez votre profil de bêta-lecteur',
    'Create beta reader profile': 'Créer un profil de bêta-lecteur',
    'Introduce yourself to beta readers with a public name, unique handle, and optional bio.':
        'Présentez-vous aux bêta-lecteurs avec un nom public, un identifiant unique et une biographie facultative.',
    'Introduce yourself to authors with a public name, unique handle, and optional bio.':
        'Présentez-vous aux auteurs avec un nom public, un identifiant unique et une biographie facultative.',
    'Manage your profile and account settings':
        'Gérez les paramètres de votre profil et de votre compte',
    'Update your name, email address, and language':
        'Modifiez votre nom, votre adresse e-mail et votre langue',
    'Update password': 'Mettre à jour le mot de passe',
    'Update the appearance settings for your account':
        'Modifiez l’apparence de votre compte',
    'Ensure your account is using a long, random password to stay secure':
        'Utilisez un mot de passe long et aléatoire pour sécuriser votre compte',
    'Please proceed with caution, this cannot be undone.':
        'Soyez prudent, cette action est irréversible.',
    'Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.':
        'La suppression de votre compte entraînera la suppression définitive de toutes ses ressources et données. Saisissez votre mot de passe pour confirmer.',
    'Thoughtful feedback before publication':
        'Des retours réfléchis avant publication',
    'Better books begin with thoughtful beta readers':
        'Les meilleurs livres commencent avec des bêta-lecteurs attentifs',
    'Find the feedback your book needs':
        'Trouvez les retours dont votre livre a besoin',
    'before publication.': 'avant sa publication.',
    'BetaReadr brings authors seeking useful, pre-publication feedback together with beta readers who want to help shape books they care about.':
        'BetaReadr réunit des auteurs en quête de retours utiles avant publication et des bêta-lecteurs désireux de contribuer aux livres qui leur tiennent à cœur.',
    'Register for BetaReadr': 'S’inscrire sur BetaReadr',
    'Go to your Dashboard': 'Accéder à votre tableau de bord',
    'In progress': 'En cours',
    Interest: 'Intérêt',
    Match: 'Correspondance',
    'Two paths, one community': 'Deux parcours, une communauté',
    'Take part in the story': 'Prenez part à l’histoire',
    'For authors': 'Pour les auteurs',
    'For beta readers': 'Pour les bêta-lecteurs',
    'Your next chapter starts here.': 'Votre prochain chapitre commence ici.',
    'Thoughtful feedback for books before publication.':
        'Des retours réfléchis sur les livres avant leur publication.',
    'BetaReadr connects authors seeking thoughtful pre-publication feedback with beta readers who want to help shape a book.':
        'BetaReadr met en relation des auteurs en quête de retours avant publication et des bêta-lecteurs qui souhaitent contribuer à façonner un livre.',
    'Create the profile that fits how you want to use BetaReadr. You can be both an author and a beta reader.':
        'Créez le profil adapté à votre usage de BetaReadr. Vous pouvez être à la fois auteur et bêta-lecteur.',
    'Present your work clearly and explain the thoughtful pre-publication feedback you need.':
        'Présentez clairement votre travail et expliquez les retours dont vous avez besoin avant publication.',
    'Create your public author profile.': 'Créez votre profil public d’auteur.',
    'Add a book you want feedback on.':
        'Ajoutez un livre sur lequel vous souhaitez des retours.',
    'Open a Beta Reading Listing with a focused Reading Brief.':
        'Publiez une annonce de bêta-lecture avec un brief précis.',
    'Share what you enjoy reading and prepare to find pre-publication books where your perspective can help.':
        'Partagez vos goûts de lecture et trouvez des livres à paraître auxquels votre point de vue peut être utile.',
    'Create your public beta reader profile.':
        'Créez votre profil public de bêta-lecteur.',
    'Use your bio to share your reading interests and experience.':
        'Utilisez votre biographie pour partager vos goûts et votre expérience de lecture.',
    'Discovery and matching with authors and their books are still to come.':
        'La découverte et la mise en relation avec les auteurs et leurs livres arrivent prochainement.',
    'Join BetaReadr as an author, a beta reader, or both—and help make pre-publication feedback more thoughtful.':
        'Rejoignez BetaReadr comme auteur, bêta-lecteur, ou les deux, et contribuez à des retours plus pertinents avant publication.',
};

export function useTranslation() {
    const page = usePage();
    const translations: Partial<
        Record<'en' | 'es' | 'fr', Record<string, string>>
    > = { es, fr };

    return {
        t: (text: string) => translations[page.props.locale]?.[text] ?? text,
    };
}
