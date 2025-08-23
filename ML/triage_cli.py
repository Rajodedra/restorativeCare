import argparse
import joblib
import pandas as pd

def predict_urgency(vitals, model, features):
    # Create a DataFrame with the input vitals
    input_data = pd.DataFrame([vitals])
    
    # Make prediction
    probability = model.predict_proba(input_data[features])[0]
    is_emergency = model.predict(input_data[features])[0]
    
    # Calculate confidence score
    confidence = probability[1] if is_emergency == 1 else probability[0]
    
    return {
        "is_emergency": bool(is_emergency),
        "confidence": float(confidence),
        "recommendation": "Immediate attention required" if is_emergency else "Patient can wait",
        "probability_emergency": float(probability[1])
    }

def main():
    parser = argparse.ArgumentParser(description='Patient Triage Prediction CLI')
    subparsers = parser.add_subparsers(dest='command', help='Command to run')
    
    # Create the parser for the "predict" command
    predict_parser = subparsers.add_parser('predict', help='Predict patient urgency')
    
    # Add arguments - using dest parameter to specify attribute names
    predict_parser.add_argument('--heart-rate', dest='heart_rate', type=float, required=True)
    predict_parser.add_argument('--blood-pressure-systolic', dest='blood_pressure_systolic', type=float, required=True)
    predict_parser.add_argument('--blood-pressure-diastolic', dest='blood_pressure_diastolic', type=float, required=True)
    predict_parser.add_argument('--temperature', type=float, required=True)
    predict_parser.add_argument('--oxygen-saturation', dest='oxygen_saturation', type=float, required=True)
    predict_parser.add_argument('--respiratory-rate', dest='respiratory_rate', type=float, required=True)
    
    args = parser.parse_args()
    
    if args.command == 'predict':
        # Load the model
        try:
            model = joblib.load('patient_triage_model.pkl')
            features = ['heart_rate', 'blood_pressure_systolic', 'blood_pressure_diastolic', 
                        'temperature', 'oxygen_saturation', 'respiratory_rate']
            
            # Collect vitals
            vitals = {}
            for feature in features:
                vitals[feature] = getattr(args, feature)
            
            # Make prediction
            result = predict_urgency(vitals, model, features)
            
            # Display results
            print("\nPrediction Results:")
            print("-" * 40)
            print(f"Emergency Status: {'EMERGENCY' if result['is_emergency'] else 'NON-EMERGENCY'}")
            print(f"Confidence: {result['confidence']:.2f}")
            print(f"Recommendation: {result['recommendation']}")
            print(f"Emergency Probability: {result['probability_emergency']:.2f}")
            
        except FileNotFoundError:
            print("Error: Model file not found. Please train the model first.")
        except Exception as e:
            print(f"Error: {str(e)}")
    else:
        parser.print_help()

if __name__ == "__main__":
    main()