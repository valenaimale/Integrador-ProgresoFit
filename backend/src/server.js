import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import authRoutes from '../routes/authRoutes.js';
import gimnasioRoutes from '../routes/gimnasioRoutes.js';
import usuarioRoutes from '../routes/usuarioRoutes.js';
import rutinaRoutes from '../routes/rutinaRoutes.js';
import ejercicioRoutes from '../routes/ejercicioRoutes.js';
import entrenadorRoutes from '../routes/entrenadorRoutes.js';
import suscripcionRoutes from '../routes/suscripcionRoutes.js';
import accesosRoutes from '../routes/accesosRoutes.js';
import claseRoutes from '../routes/claseRoutes.js';
import mercadopagoRoutes from '../routes/mercadopago.routes.js';

dotenv.config();
//todas las peticiones al back end se reciben aca
const app = express();

//Middlewares
app.use(cors()); //Permite la conexión desde el frontend

// Raw body parser para webhook de MP — DEBE ir ANTES de express.json()
// para preservar el body crudo que necesita la validación HMAC
app.use('/mercadopago/webhook', express.raw({ type: 'application/json' }), (req, res, next) => {
  req.rawBody = req.body.toString('utf-8');
  try { req.body = JSON.parse(req.rawBody); } catch { /* fallback: raw body queda como string */ }
  next();
});

app.use(express.json()); // Parsea el body de las request a JSON

//Rutas por las que pasara mi App
app.use('/auth', authRoutes);
app.use('/gimnasios', gimnasioRoutes);
app.use('/usuarios', usuarioRoutes);
app.use('/rutinas', rutinaRoutes);
app.use('/ejercicios', ejercicioRoutes);
app.use('/entrenadores', entrenadorRoutes);
app.use('/suscripciones', suscripcionRoutes);
app.use('/accesos', accesosRoutes);
app.use('/clases', claseRoutes);
app.use('/mercadopago', mercadopagoRoutes);

//Ruta de prueba
app.get('/', (req, res) => {
  res.json({ message: 'API ProgresoFit funcionando' });
});


//Manejo de errores global:
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ error: 'Algo salio mal en el servidor' });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () =>{
  console.log(`Servidor corriendo en http://localhost:${PORT}`);
})