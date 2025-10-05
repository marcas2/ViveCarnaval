<?php
namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\SecurityBundle\Security; 
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(Security $security): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'nombre' => $user->getNombre(),
            'correo' => $user->getCorreo(),
            'foto' => $user->getFoto(),
            'biografia' => $user->getBiografia(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/api/profile', name: 'api_profile', methods: ['GET', 'POST'])]
    public function profile(
        Request $request,
        EntityManagerInterface $em,
        Security $security,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'No autenticado'], 401);
        }

        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $biografia = $request->request->get('biografia');
            $contrasena = $request->request->get('contrasena');
            $foto = $request->files->get('foto');

            if ($nombre) {
                $user->setNombre($nombre);
            }
            if ($biografia) {
                $user->setBiografia($biografia);
            }

            if ($contrasena) {
                $user->setPassword($hasher->hashPassword($user, $contrasena));
            }

            // ✅ Subida de foto
            if ($foto) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                if (!file_exists($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }

                $filename = uniqid() . '.' . $foto->guessExtension();
                try {
                    $foto->move($uploadsDir, $filename);
                    $user->setFoto('/uploads/' . $filename);
                } catch (FileException $e) {
                    return new JsonResponse(['error' => 'Error subiendo archivo'], 500);
                }
            }

            $em->persist($user);
            $em->flush();

            return new JsonResponse(['message' => 'Perfil actualizado con éxito']);
        }

        // ✅ GET (cargar perfil)
        return new JsonResponse([
            'nombre' => $user->getNombre(),
            'biografia' => $user->getBiografia(),
            'foto' => $user->getFoto() ?? '/images/default-avatar.png',
            'correo' => $user->getCorreo(),
        ]);
    }
}