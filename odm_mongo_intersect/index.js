import mongoose from 'mongoose'
import { getdata } from './api.js'
const { Schema, model } = mongoose

const studentSchema = new mongoose.Schema(
  {
    student_data: {
      ID: {
        type: String,
        required: true,
        index: true
      },
      name: {
        type: String,
        required: true
      },
      dept_name: {
        type: String,
        required: true
      },
      tot_cred: {
        type: Number,
        required: true
      }
    },
    status_matricula: {
      type: String,
      enum: ['No matriculado', 'Matriculado'],
      default: 'No matriculado'
    },
    matricula: [
      {
        type: String,
        enum: ['Primera', 'Segunda', 'Tercera']
      }
    ],
    academic_info: {
      total_courses: Number,
      unique_semesters: [String],
      years_enrolled: [Number]
    },
    courses: [
      {
        course_id: { type: String, required: true },
        sec_id: { type: String, required: true },
        semester: {
          type: String,
          enum: ['Fall', 'Spring', 'Summer'],
          required: true
        },
        year: {
          type: Number,
          required: true
        },
        grade: String
      }
    ]
  },
  {
    timestamps: true
  }
)

let Student = mongoose.model('Student', studentSchema)

const uri = 'mongodb://127.0.0.1:27017/intersect'
const options = {
  autoIndex: true,
  maxPoolSize: 10,
  serverSelectionTimeoutMS: 5000,
  socketTimeoutMS: 45000,
  family: 4
}

try {
  await mongoose.connect(uri, options)
  console.log('Connected to MongoDB')

  // Clear existing data
  await Student.deleteMany({})

  // Get data from API
  const data = await getdata()

  // Transform and save the data
  const students = data.students.map(student => {
    const uniqueSemesters = [...new Set(student.courses?.map(c => c.semester) || [])]
    const uniqueYears = [...new Set(student.courses?.map(c => c.year) || [])]

    return {
      student_data: student.student_data,
      status_matricula: student.courses?.length > 0 ? 'Matriculado' : 'No matriculado',
      matricula: ['Primera'],
      academic_info: {
        total_courses: student.courses?.length || 0,
        unique_semesters: uniqueSemesters,
        years_enrolled: uniqueYears
      },
      courses: student.courses || []
    }
  })

  // Insert the transformed data
  const result = await Student.insertMany(students, {
    ordered: false,
    validateBeforeSave: true
  })
  console.log(`Inserted ${result.length} students successfully`)
} catch (error) {
  console.error('Error:', error)
} finally {
  await mongoose.connection.close()
  process.exit(0)
}
