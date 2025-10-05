<?php

namespace App\Controller\Login;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\Login\RolesRepository;
use App\Repository\Login\UsuariosRepository;
use App\Entity\Login\Usuarios;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;



final class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function indexLogin(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login_auth/index.html.twig', [
           'last_username' => $lastUsername,
           'error'         => $error,
        ]);
    }
    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(){

    }

    

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
public function register(Request $request, UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $em, RolesRepository $rolesRepository,
    UsuariosRepository $usuariosRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $correo = $data['correo'] ?? null;
    $contrasena = $data['contrasena'] ?? null;
    $nombre = $data['nombre'] ?? null;
    $rolName = $data['rol'] ?? 'ROLE_USER';

    if (!$correo || !$contrasena || !$nombre) {
        return new JsonResponse(['error' => 'Faltan campos requeridos'], 400);
    }

    if ($usuariosRepository->findOneBy(['correo' => $correo])) {
        return new JsonResponse(['error' => 'Usuario ya existe'], 400);
    }

    $rol = $rolesRepository->findOneBy(['rol' => $rolName]);
    if (!$rol) {
        return new JsonResponse(['error' => 'Rol no válido'], 400);
    }

    $usuario = new Usuarios();
    $usuario->setNombre($nombre)
            ->setCorreo($correo)
            ->setContrasena($passwordHasher->hashPassword($usuario, $contrasena))
            ->setRol($rol);

    $em->persist($usuario);
    $em->flush();

    return new JsonResponse(['message' => 'Usuario registrado correctamente'], 201);
}

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // LexikJWT maneja la autenticación automáticamente
        $user = $this->getUser();
        
        return new JsonResponse([
            'message' => 'Login exitoso',
            'token' => $this->jwtTokenManager->create($user) // Si usas JWT
        ], 200);
    }
}
