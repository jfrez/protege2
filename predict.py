# predict.py
import sys
import json
import pickle
import pandas as pd
import joblib

import warnings
warnings.filterwarnings("ignore", category=UserWarning, module='sklearn.base')

modelo = joblib.load('modelo_random_forest.pkl')

# Read input from command line arguments
input_data = sys.stdin.read()
data = json.loads(input_data)
riesgo = data['riesgo']

# Factores de riesgo y protección separados por categorías
factores_riesgo_personales = [
    "Historia Familiar de Problemas de Salud Mental",
    "Antecedentes de Abuso de Sustancias",
    "Problemas de Comportamiento y de Regulación Emocional",
    "Baja Autoestima",
    "Enfermedades Crónicas o Discapacidades Físicas",
    "Estrés Prolongado o Traumático"
]

factores_riesgo_familiares = [
    "Conflictos Familiares y Violencia Doméstica",
    "Falta de Apoyo Emocional y Supervisión",
    "Abuso Físico, Emocional o Sexual",
    "Pérdida de uno o ambos Padres",
    "Padres con Problemas de Salud Mental o Abuso de Sustancias"
]

factores_riesgo_sociales = [
    "Exclusión Social y Falta de Redes de Apoyo",
    "Pobreza y Dificultades Económicas",
    "Experiencias de Discriminación y Estigmatización",
    "Influencias Negativas de Pares y Amigos",
    "Ambientes Escolares Poco Seguros o Violentos"
]

factores_riesgo_ambientales = [
    "Vivienda Inadecuada o Condiciones de Vida Peligrosas",
    "Acceso Limitado a Servicios de Salud y Educación",
    "Desastres Naturales y Crisis Humanitarias",
    "Exposición a Violencia Comunitaria"
]

factores_proteccion_personales = [
    "Buena Salud Física y Mental",
    "Habilidades de Afrontamiento y Manejo del Estrés",
    "Alta Autoestima y Autoconfianza",
    "Buen Rendimiento Académico y Habilidades Cognitivas",
    "Participación en Actividades Recreativas y Deportivas"
]

factores_proteccion_familiares = [
    "Relaciones Familiares Cálidas y de Apoyo",
    "Supervisión y Guía Adecuada por Parte de los Padres",
    "Comunicación Abierta y Efectiva en la Familia",
    "Presencia de al Menos un Adulto Significativo y de Confianza",
    "Prácticas de Crianza Positivas y Consistentes"
]

factores_proteccion_sociales = [
    "Redes de Apoyo Social Sólidas (Amigos, Comunidad)",
    "Participación en Grupos y Actividades Comunitarias",
    "Ambiente Escolar Seguro y de Apoyo",
    "Acceso a Servicios de Salud Mental y Otros Recursos",
    "Experiencias de Éxito y Reconocimiento Social"
]

factores_proteccion_ambientales = [
    "Entorno Físico Seguro y Saludable",
    "Políticas y Programas Comunitarios de Apoyo",
    "Acceso a Educación de Calidad y Oportunidades de Empleo",
    "Servicios de Salud Accesibles y de Buena Calidad",
    "Programas de Prevención y Promoción de la Salud"
]

datos = pd.DataFrame([riesgo], columns=factores_riesgo_personales + factores_riesgo_familiares +
                         factores_riesgo_sociales + factores_riesgo_ambientales +
                         factores_proteccion_personales + factores_proteccion_familiares +
                         factores_proteccion_sociales + factores_proteccion_ambientales)


# Make a prediction
prediction = modelo.predict(datos)
# Ensure riesgo is in the expected format

# Output the prediction
print(json.dumps({'prediction': prediction.tolist() if hasattr(prediction, 'tolist') else prediction}))
