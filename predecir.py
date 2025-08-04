import sys
import pandas as pd
import joblib
from sklearn.preprocessing import LabelEncoder
from sqlalchemy import create_engine

engine = create_engine(
    'mssql+pyodbc://sa:YourStrong!Passw0rd@db/protege?driver=ODBC+Driver+18+for+SQL+Server&TrustServerCertificate=yes'
)


# Leer el evaluation_id desde argumentos
if len(sys.argv) < 2:
    print("Error: Debe proporcionar un evaluation_id")
    sys.exit(1)

evaluation_id = int(sys.argv[1])


# Consulta para obtener las mismas columnas utilizadas en el entrenamiento
# Omitimos evaluador, profesion, centro, fecha_evaluacion, valoracion_global
query = f"""
SELECT 
    e.edad, 
    e.sexo, 
    e.diversidad,
    e.escolaridad,
    e.region,
    e.localidad,
    e.zona,
    e.diversidad_cual,
    e.nacionalidad,
    e.pais_origen,
    e.situacion_migratoria,
    e.pueblo,
    e.pueblo_cual,
    e.convivencia,
    e.maltrato,
    e.otro_maltrato,
    e.relacion_perpetrador,
    e.otro_relacion,
    e.fuente,
    i.enfermedades_cronicas_discapacidad, 
    i.alteraciones_graves_comportamiento,
    i.desvinculacion_ausentismo_escolar, 
    i.denuncias_ingresos_maltrato_previo,
    i.terapia_nna,
    f.problemas_salud_mental_cuidadores,
    f.consumo_problematico_cuidadores,
    f.violencia_pareja,
    f.historia_maltrato_cuidadores,
    f.antecedentes_penales_cuidadores,
    f.dificultades_soporte_social,
    f.estres_supervivencia,
    f.deficiencia_habilidades_cuidado,
    f.actitudes_negativas_nna,
    f.atencion_prenatal_retrasada_ausente,
    f.inestabilidad_cuidados,
    f.ideacion_suicida_cuidadores,
    f.actitudes_negativas_intervencion,
    f.compromiso_colaborativo,
    f.extrema_minimizacion_negacion_maltrato,
    f.terapia_cuidadores,
    f.reunificaciones_fallidas,
    c.historia_maltrato_perpetrador,
    c.presencia_pares_confianza_nna,
    c.involucramiento_previo_servicio_proteccion
FROM evaluacion e
JOIN factores_individuales i ON e.id = i.evaluacion_id
JOIN factores_familiares f ON e.id = f.evaluacion_id
JOIN factores_contextuales c ON e.id = c.evaluacion_id
WHERE e.id = {evaluation_id}
"""

df = pd.read_sql(query, engine)

if df.empty:
    print("No se encontró evaluación con ese ID")
    sys.exit(1)

# Mapeos usados durante el entrenamiento
mapping_abc = {'a':0,'b':1,'c':2,'d':3}
target_mapping_inv = {0:'bajo',1:'medio',2:'alto'}

# Preprocesar las variables igual que en el entrenamiento
for col in df.columns:
    if df[col].dtype == object:
        unique_vals = df[col].dropna().unique()
        # Si todos los valores son 'a','b','c','d'
        if len(unique_vals) > 0 and all(val in mapping_abc for val in unique_vals):
            df[col] = df[col].map(mapping_abc)
        else:
            # LabelEncoder para otras columnas categóricas
            le = LabelEncoder()
            df[col] = le.fit_transform(df[col].astype(str))

# Cargar el modelo
clf = joblib.load('modelo_rf.pkl')

# Hacer la predicción
y_pred = clf.predict(df)
pred_class = target_mapping_inv[y_pred[0]]

print(pred_class)
