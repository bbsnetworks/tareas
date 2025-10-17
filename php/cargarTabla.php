<?php
                include("conexion.php");

                if ($conexion->connect_error) {
                    die("Conexión fallida: " . $conexion->connect_error);
                }
                
                $sql = "select * from eventos where month(start)=$_POST[mes] and year(start)=$_POST[year] order by start desc;";
                //$sql = "select * from users u inner join gastos g where u.iduser = g.iduser;";
                $result = $conexion->query($sql);

                

                
                if ($result->num_rows > 0) {
                    echo "<table id='contratos-table' class='table table-auto table-dark table-striped w-full descripcion-amplia'>";
                    echo "<thead>
                        <tr>
                            <th class='w-16'>ID</th>
                            <th class='w-40 truncate'>Título</th>
                            <th class='w-20'>Estado</th>
                            <th class='w-32'>Inicio</th>
                            <th class='w-32'>Fin</th>
                            <th class='w-40 truncate'>Ubicación</th>
                            <th class='w-60 truncate'>Comentarios</th>
                            <th class='w-16'>Editar</th>
                            <th class='w-16'>Mostrar</th>
                        </tr>
                    </thead>";
                    echo "<tbody>";
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["title"] . "</td>";
                        switch($row["estado"]){
                            case "creado":
                                echo "<td class='text-blue-500'>creado <i class='bi bi-plus-circle-fill'></i></td>";
                                break;
                            case "proceso":
                                echo "<td class='text-yellow-500'>En proceso <i class='bi bi-hammer'></i></td>";
                                break;
                            case "terminado":
                                echo "<td class='text-green-500'>Terminado <i class='bi bi-check-circle-fill'></i></td>";
                                break;
                            case "cancelado":
                                echo "<td class='text-red-500'>Cancelado <i class='bi bi-x-circle-fill'></i></td>";
                                break;
                        }
                        echo "<td>" . $row["start"] . "</td>";
                        if($row["end"]=='2000-01-01 01:01:00' || $row["end"]==''){
                            echo "<td>Sin Fecha</td>";
                        }else{
                            echo "<td>" . $row["end"] . "</td>";
                        }
                        
                        echo "<td>" . $row["location"] . "</td>";
                        echo "<td>" . $row["comentarios"] . "</td>";
                        echo "<td class='centrar'><button class='focus:outline-none text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:focus:ring-yellow-900' data-bs-toggle='modal' data-bs-target='#modalEditar' onclick=\"openEditModal(" . $row["id"] . ")\"><i class='bi bi-pencil-square'></i> </button></td>";
                        echo "<td class='centrar'><button class='text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700' onclick=\"showEventModal(" . $row["id"] . ")\"><i class='bi bi-eye-fill'></i></button></td>";
                        echo "</tr>";
                    }
                    
                    echo "</tbody>";
                    echo "</table>";
                } else {
                    echo "0 resultados";
                }
                
                $conexion->close();

                echo("<script>
    $('#contratos-table').DataTable({
        responsive: true,
        columnDefs: [
            { targets: 0, width: '5%' },   // ID
            { targets: 1, width: '20%' },  // Título
            { targets: 2, width: '15%' },  // Inicio
            { targets: 3, width: '15%' },  // Fin
            { targets: 4, width: '20%' },  // Ubicación
            { targets: 5, width: '10%' },  // Estado
            { targets: 6, width: '25%' },  // Comentarios
            { targets: 7, width: '5%' },   // Editar
            { targets: 8, width: '5%' }    // Mostrar
        ]
    });
</script>");
                
            ?>