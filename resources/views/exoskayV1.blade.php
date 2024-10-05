@extends('layouts.app')

@section('subtitle', 'Star Field')

@section('content_body')
    <div id="threejs-container">
        <div id="info">
            <div id="text_tutorial">
                Haz clic en una estrella para ver sus coordenadas. Usa el mouse para rotar y el scroll para hacer zoom.
                <button class="btn btn-outline-dark btn-sm" style="color:white" onclick="document.getElementById('text_tutorial').style.display = 'none';">X</button>
            </div>
        </div>
        <div id="star-info" style="position: absolute; color: white; font-family: Arial, sans-serif; z-index: 1;"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script>
        // Escena, cámara y renderizador
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer();
        renderer.setSize(window.innerWidth, window.innerHeight);

        // Asegúrate de que el canvas se renderice dentro del contenedor adecuado
        const container = document.getElementById('threejs-container');
        container.appendChild(renderer.domElement);

        // Crear un planeta (simulado con una esfera grande)
        const planetGeometry = new THREE.SphereGeometry(10, 32, 32);
        const planetMaterial = new THREE.MeshBasicMaterial({ color: 0x003366 });
        const planet = new THREE.Mesh(planetGeometry, planetMaterial);
        scene.add(planet);

        // Posiciona la cámara en el planeta (ligeramente sobre la superficie)
        camera.position.set(0, 20, 50);
        camera.lookAt(planet.position);

        // Controles de órbita para rotar y hacer zoom
        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true; // Para suavizar el movimiento
        controls.dampingFactor = 0.05;
        controls.enableZoom = true;
        controls.autoRotate = false;
        controls.enablePan = false;

        // Array para las estrellas
        const stars = [];

        // Función para generar estrellas aleatorias alrededor del planeta
        function createStarField() {
            const starGeometry = new THREE.SphereGeometry(1, 16, 16); // Aumenta el tamaño de las estrellas

            for (let i = 0; i < 100; i++) {
                const magnitude = Math.random() * 5 + 1; // Magnitud aleatoria
                const starMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff });

                const star = new THREE.Mesh(starGeometry, starMaterial);

                // Datos adicionales de la estrella
                star.userData = {
                    name: `Estrella-${i + 1}`,  // Nombre simulado
                    magnitude: magnitude.toFixed(2) // Magnitud simulada
                };

                // Colocación aleatoria de las estrellas
                const radius = Math.random() * 100 + 50; // Están lejos del planeta
                const theta = Math.random() * Math.PI * 2; // Ángulo horizontal
                const phi = Math.random() * Math.PI; // Ángulo vertical

                // Coordenadas esféricas a cartesianas
                star.position.x = radius * Math.sin(phi) * Math.cos(theta);
                star.position.y = radius * Math.cos(phi);
                star.position.z = radius * Math.sin(phi) * Math.sin(theta);

                scene.add(star);
                stars.push(star); // Añadir la estrella al array
            }
        }

        createStarField();

        // Raycaster para la detección de clics en las estrellas
        const raycaster = new THREE.Raycaster();
        const mouse = new THREE.Vector2();

        // Evento de clic
        window.addEventListener('click', (event) => {
            // Obtener el contenedor del canvas
            const container = document.getElementById('threejs-container');

            // Calcular las coordenadas del mouse relativas al contenedor
            const rect = container.getBoundingClientRect();
            const offsetX = event.clientX - rect.left;
            const offsetY = event.clientY - rect.top;

            // Normalizar coordenadas del mouse
            mouse.x = (offsetX / container.clientWidth) * 2 - 1;
            mouse.y = -(offsetY / container.clientHeight) * 2 + 1;

            // Actualiza el raycaster con las coordenadas de la cámara y el ratón
            raycaster.setFromCamera(mouse, camera);

            // Intersecciones con los objetos en la escena
            const intersects = raycaster.intersectObjects([planet, ...stars]); // Incluye el planeta en la detección

            const starInfo = document.getElementById('star-info');
            if (intersects.length > 0) {
                const intersectedObject = intersects[0].object;
                if (intersectedObject === planet) {
                    console.log('Has pulsado el planeta azul');
                } else {
                    const star = intersectedObject;
                    const { x, y, z } = star.position;
                    const { name, magnitude } = star.userData;

                    starInfo.innerText = `Nombre: ${name} | Magnitud: ${magnitude} | Coordenadas: X: ${x.toFixed(2)}, Y: ${y.toFixed(2)}, Z: ${z.toFixed(2)}`;
                }
            } else {
                const mouseCoords = `Coordenadas del mouse: X: ${event.clientX}, Y: ${event.clientY}`;
                starInfo.innerText = mouseCoords;
            }

            // Ajustar el posicionamiento del elemento #star-info
            starInfo.style.left = event.clientX + 'px';
            starInfo.style.top = event.clientY + 'px';
            starInfo.style.display = 'block';
        });

        // Animar las estrellas (parpadeo leve)
        function animateStars() {
            stars.forEach(star => {
                const scaleFactor = 0.1 * Math.sin(Date.now() * 0.001 + Math.random()) + 1;
                star.scale.set(scaleFactor, scaleFactor, scaleFactor);
            });
        }

        // Render loop
        function animate() {
            requestAnimationFrame(animate);
            controls.update(); // Actualizar los controles en cada cuadro
            animateStars(); // Animar las estrellas
            renderer.render(scene, camera);
        }

        animate();

        // Ajustar el tamaño del renderizador al redimensionar la ventana
        window.addEventListener('resize', () => {
            const width = window.innerWidth;
            const height = window.innerHeight;
            renderer.setSize(width, height);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
        });
    </script>
@stop

@section('footer')
    <div class="custom-footer">
        <div class="float-right">
            Version: {{ config('app.version', '1.0.0') }}
        </div>
        <strong>
            <a href="{{ config('app.company_url', '#') }}">
                Button :)
            </a>
        </strong>
    </div>
@stop
@push('css')
    <style>
        body, html {
            height: 100%;
            overflow: hidden; /* Elimina el scroll vertical */
        }
        canvas { display: block; }
        #info {
            position: absolute;
            color: white;
            font-family: Arial, sans-serif;
            z-index: 1;
            top: 50px; /* Ajusta esta propiedad para mover el texto hacia arriba */
            left:calc(50vw - 400px);
            white-space: nowrap; /* Evita que el texto se divida en varias líneas */
            text-align: center;
        }

        #threejs-container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden; /* Asegura que no haya desplazamientos adicionales */
        }
        .content-wrapper {
            height: calc(100vh);
            overflow: hidden;
            padding: 0 !important;
        }
        .content{
            margin: 0 !important;
            padding: 0 !important;
        }
        .container-fluid{
            margin: 0 !important;
            padding: 0 !important;
        }
        .container{
            margin: 0 !important;
            padding: 0 !important;
        }
        .content-header{
            padding: 0 !important;
        }
        .main-footer{
            margin: 0 !important;
            padding: 0 !important;
        }
        .custom-footer {
            margin: 0 !important;
            padding: 0 !important;
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Fondo transparente */
            color: white;
            text-align: center;
            padding: 10px 0;
            z-index: 2; /* Asegura que esté encima del contenido */
        }
        .custom-footer a {
            color: white;
            text-decoration: none;
        }
        .main-header.navbar {
            padding: 0 !important;
            border: none !important;
            background-color: rgba(69,77,85, 0); /* Fondo transparente al 50% */
            position: absolute;
            top: 0;
            left: 0;
            z-index: 3; /* Asegura que esté encima del contenido */
        }
        .main-header a {
            color: white;
            text-decoration: none;
        }

    #star-info {
        display: none;
        position: absolute;
        color: white;
        font-family: Arial, sans-serif;
        z-index: 1;
        background-color: rgba(0, 0, 0, 0.7);
        padding: 5px;
        border-radius: 5px;
        pointer-events: none; /* Asegura que el elemento no interfiera con los clics */
    }
    </style>
@endpush
@push('js')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function adjustHeaderWidth() {
            const contentWrapper = document.querySelector('.content-wrapper');
            const mainHeader = document.querySelector('.main-header.navbar');
            const contentBody = document.querySelector('.content-wrapper .content');
            if (contentWrapper && mainHeader && contentBody) {
                const contentWidth = contentWrapper.offsetWidth;
                mainHeader.style.width = contentWidth + 'px';
                contentBody.style.width = contentWidth + 'px';
            }
        }

        // Ajustar el ancho inicialmente
        adjustHeaderWidth();

        // Ajustar el ancho cuando se redimensiona la ventana
        window.addEventListener('resize', adjustHeaderWidth);

        // Ajustar el ancho cuando se colapsa o se expande el sidebar
        const sidebar = document.querySelector('.main-sidebar');
        if (sidebar) {
            sidebar.addEventListener('transitionend', adjustHeaderWidth);
        }
    });
    </script>
@endpush