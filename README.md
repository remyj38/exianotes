exianotes
=========


Classe Auth : 
  Lorsque l'objet est créé, la fonction restore_session est appelée.

  private crypt($mot_de_passe) ==> Retourne le mot de passe hashé

  public getUser() ==> Retourne le nom d'utilisateur (si non connecté : "invité")

  public login($user, $password, $cookie) ==> connecte l'utilisateur avec un nom d'utilisateur ou email dans la variable $user. Retourne TRUE si la connexion à réussie ou FALSE si elle a échouée. Si $cookie=TRUE, les cookies sont générés.

  restore_session() ==> Restore la session a partir des cookies.
